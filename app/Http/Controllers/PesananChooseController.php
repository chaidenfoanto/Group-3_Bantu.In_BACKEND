<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LocationModel;
use App\Models\TukangModel;
use App\Models\PesananModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PesananChooseController extends Controller
{
    public function ChooseDate(Request $request) {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        if (!$user->id_user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Harus terdaftar sebagai user.',
            ], 403);
        }

        $validatorRules = [
            'jenis_servis' => 'required|array',
            'jenis_servis.*.jenis' => 'required|string|exists:biaya,jenis_servis',
            'jenis_servis.*.kuantitas' => 'required|integer|min:1',
            'alamat_servis' => 'required|string',
            'metode_pembayaran' => 'required|in:Cash,Non-tunai',
            'waktu_req_by_user' => 'required|date|after:now',
            'description_problem' => 'nullable|string',
            'photo_problem_issue' => 'nullable|image|max:2048'
        ];

        if ($request->has('id_tukang')) {
            $validationRules['id_tukang'] = 'required|exists:tukang,id_tukang';
        }

        $validator = Validator::make($request->all(), $validatorRules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 400);
        }

        $locate = LocationModel::where('id_user', $user->id_user)->first();
        if (!$locate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lokasi untuk user tidak ditemukan.',
            ], 404);
        }

        $origin = json_decode($locate->origin, true);
        $radius = 10;

        if ($request->has('id_tukang')) {
            // Jika id_tukang disediakan, gunakan tukang tersebut
            $selectedTukang = TukangModel::find($request->id_tukang);
            
            if (!$selectedTukang) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tukang tidak ditemukan.',
                ], 404);
            }

            $tukangLocation = is_string($selectedTukang->tukang_location)
                ? json_decode($selectedTukang->tukang_location, true)
                : $selectedTukang->tukang_location;
            
            $distance = $this->calculateDistance($origin, $tukangLocation);
            
            if ($distance > $radius) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tukang berada di luar jangkauan radius ' . $radius . ' km.',
                ], 400);
            }

            $selectedTukang->distance = $distance;
        } else {
            // Jika tidak ada id_tukang, cari tukang terdekat secara random
            $tukangs = TukangModel::all();
            
            $tukangTerdekat = $tukangs->map(function ($tukang) use ($origin) {
                $tukangLocation = is_string($tukang->tukang_location)
                    ? json_decode($tukang->tukang_location, true)
                    : $tukang->tukang_location;
                $tukang->distance = $this->calculateDistance($origin, $tukangLocation);
                return $tukang;
            })->filter(function ($tukang) use ($radius) {
                return $tukang->distance <= $radius;
            })->values();

            if ($tukangTerdekat->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada tukang yang berada dalam jangkauan radius.',
                ], 404);
            }

            $selectedTukang = $tukangTerdekat->random();
        }

        $totalBiaya = 0;
        $biayaAdmin = 1000;
        $detailPesananData = [];
        $latestBiaya = null;

        foreach ($request->jenis_servis as $servis) {
            $biaya = DB::table('biaya')
                ->where('jenis_servis', $servis['jenis'])
                ->first();

            if (!$biaya) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Biaya untuk layanan "' . $servis['jenis'] . '" tidak ditemukan.',
                ], 404);
            }

            $subtotal = $biaya->biaya_servis * $servis['kuantitas'];
            $totalBiaya += $subtotal;
            $biayaAdmin = $biaya->biaya_admin;
            $latestBiaya = $biaya;

            $detailPesananData[] = [
                'nama_layanan' => $servis['jenis'],
                'harga_layanan' => $biaya->biaya_servis,
                'subtotal' => $subtotal,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $totalBiaya += $biayaAdmin;

        // Buat data pesanan utama
        $idPesanan = Str::uuid()->toString();
        $pesananData = [
            'id_pesanan' => $idPesanan,
            'id_user' => $user->id_user,
            'id_tukang' => $selectedTukang->id_tukang,
            'id_biaya' => $latestBiaya->id_biaya,
            'waktu_pesan' => now(),
            'alamat_servis' => $request->alamat_servis,
            'metode_pembayaran' => $request->metode_pembayaran,
            'kuantitas' => collect($request->jenis_servis)->sum('kuantitas'),
            'waktu_req_by_user' => Carbon::parse($request->waktu_req_by_user)->format('Y-m-d H:i:s'),
            'description_problem' => $request->description_problem,
            'photo_problem_issue' => $request->hasFile('photo_problem_issue') 
                ? $request->file('photo_problem_issue')->store('public/pesanan') 
                : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];        

        try {
            DB::beginTransaction();
            
            // Simpan pesanan utama
            DB::table('pesanan')->insert($pesananData);
            
            // Simpan detail pesanan
            foreach ($detailPesananData as $detail) {
                DB::table('detail_pesanan')->insert([
                    'id_pesanan' => $idPesanan,
                    'nama_layanan' => $detail['nama_layanan'],
                    'harga_layanan' => $detail['harga_layanan'],
                    'subtotal' => $detail['subtotal'],
                    'created_at' => $detail['created_at'],
                    'updated_at' => $detail['updated_at'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibuat.',
                'data' => [
                    'pesanan' => $pesananData,
                    'detail_pesanan' => $detailPesananData,
                    'total_biaya' => $totalBiaya,
                    'biaya_admin' => $biayaAdmin,
                    'tukang' => [
                        'id_tukang' => $selectedTukang->id_tukang,
                        'distance' => round($selectedTukang->distance, 2) . ' km'
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membuat pesanan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateDistance($origin, $tukangLocation)
    {
        $lat1 = $origin['lat'];
        $lng1 = $origin['lng'];
        $lat2 = $tukangLocation['lat'];
        $lng2 = $tukangLocation['lng'];

        $earthRadius = 6371; // Radius bumi dalam kilometer

        // Menghitung jarak dalam derajat
        $latDifference = deg2rad($lat2 - $lat1);
        $lngDifference = deg2rad($lng2 - $lng1);

        // Menghitung jarak menggunakan Haversine formula
        $a = sin($latDifference / 2) * sin($latDifference / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDifference / 2) * sin($lngDifference / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c; // Jarak dalam kilometer

        return $distance;
    }
}