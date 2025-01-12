<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LocationModel;
use App\Models\TukangModel;
use App\Models\User;
use App\Events\UpdatedLocationTukang;
use App\Events\StartLocationTukang;
use App\Events\EndLocationTukang;
use Illuminate\Support\Facades\Validator;
use App\Models\PesananModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class LocationController extends Controller
{
   /**
     * Ambil lokasi tukang berdasarkan ID perjalanan.
     */
    /**
     * Menampilkan lokasi tukang terdekat berdasarkan id_user.
     *
     * @param string $id_user
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Ambil user yang terautentikasi
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        // Validasi data input
        $validator = Validator::make($request->all(), [
            'origin' => 'required|array',
            'origin.lat' => 'required|numeric',
            'origin.lng' => 'required|numeric',
            'destination' => 'required|array',
            'destination.lat' => 'required|numeric',
            'destination.lng' => 'required|numeric',
            'destination_name' => 'required|string',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Buat entri lokasi baru untuk user yang terautentikasi
        $locate = LocationModel::create([
            'id_user' => $user->id_user, // Foreign key dari tabel users
            'origin' => json_encode($request->origin),
            'destination' => json_encode($request->destination),
            'destination_name' => $request->destination_name,
            'is_started' => false,  // Status default
            'is_completed' => false,  // Status default
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi berhasil disimpan.',
            'data' => $locate,
        ], 201);
    }




    /**
     * Menghitung jarak antara dua titik lokasi menggunakan formula haversine.
     *
     * @param array $point1
     * @param array $point2
     * @return float
     */
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
    

    public function start(Request $request)
    {
        // Ambil user yang terautentikasi
        $user = $request->user();  // Mendapatkan user yang terautentikasi melalui middleware auth

        // Cari lokasi berdasarkan user
        $locate = LocationModel::where('id_user', $user->id_user)->first();

        if (!$locate) {
            return response()->json(['message' => 'Lokasi tidak ditemukan untuk user ini.'], 404);
        }

        if ($locate->is_started) {
            return response()->json(['message' => 'Perjalanan telah dimulai sebelumnya.'], 400);
        }

        if (!$locate->id_tukang) {
            return response()->json(['message' => 'Tidak ada tukang yang ditugaskan untuk perjalanan ini.'], 400);
        }

        // Update status perjalanan menjadi mulai
        $locate->update(['is_started' => true]);

        // Dispatch event untuk memulai perjalanan
        StartLocationTukang::dispatch($locate, $user);

        return response()->json([
            'message' => 'Perjalanan telah dimulai.',
            'locate' => $locate->load('tukang.user') // Mengambil data tukang yang terkait
        ], 200);
    }

    public function end(Request $request)
    {
        // Ambil user yang terautentikasi
        $user = $request->user();  // Mendapatkan user yang terautentikasi melalui middleware auth

        // Cari lokasi berdasarkan user
        $locate = LocationModel::where('id_user', $user->id_user)->first();

        if (!$locate) {
            return response()->json(['message' => 'Lokasi tidak ditemukan untuk user ini.'], 404);
        }

        if (!$locate->is_started) {
            return response()->json(['message' => 'Perjalanan belum dimulai.'], 400);
        }

        if ($locate->is_completed) {
            return response()->json(['message' => 'Perjalanan telah selesai sebelumnya.'], 400);
        }

        // Update status perjalanan menjadi selesai
        $locate->update(['is_completed' => true]);

        // Dispatch event untuk mengakhiri perjalanan
        EndLocationTukang::dispatch($locate, $user);

        return response()->json([
            'message' => 'Perjalanan telah selesai.',
            'locate' => $locate->load('tukang.user') // Mengambil data tukang yang terkait
        ], 200);
    }




    /**
     * Perbarui lokasi tukang saat perjalanan berlangsung.
     */
    // public function location(Request $request, LocationModel $locate)
    // {
    //     $request->validate([
    //         'tukang_location' => 'required'
    //     ]);

    //     $locate->update([
    //         'tukang_location' => $request->tukang_location,
    //     ]);

    //     $locate->load('tukang.user');

    //     UpdatedTukangLocation::dispatch($trip, $request->user());

    //     return $locate;
    // }

    public function updateLocation(Request $request)
    {
        $tukang = auth()->user();

        if (!$tukang) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }
        // Validasi input yang diperlukan
        $request->validate([
            'tukang_location' => 'required|array',
            'tukang_location.lat' => 'required|numeric',
            'tukang_location.lng' => 'required|numeric',
        ]);

        // $tukang = TukangModel::findOrFail($id_tukang);

        // Mencari tukang berdasarkan id_tukang
        // $tukang = TukangModel::findOrFail($id_tukang);

        // Update lokasi tukang
        $tukang->update([
            'tukang_location' => $request->tukang_location,
        ]);

        // Menyiarkan event untuk broadcasting ke klien
        UpdatedLocationTukang::dispatch($tukang, $request->user());

        // Kembalikan respon untuk menunjukkan lokasi tukang berhasil diperbarui
        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi tukang berhasil diperbarui',
            'data' => $tukang
        ]);
    }

    public function getNearestTukang(Request $request)
    {
        // Ambil user yang terautentikasi
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        // Cari lokasi berdasarkan id_user
        $locate = LocationModel::where('id_user', $user->id_user)->first();

        if (!$locate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lokasi untuk user tidak ditemukan.',
            ], 404);
        }

        $origin = json_decode($locate->origin, true);
        $radius = 2;

        $tukangs = TukangModel::all();

        $tukangTerdekat = $tukangs->map(function ($tukang) use ($origin) {
            // Pastikan decode JSON string ke array
            $tukangLocation = is_string($tukang->tukang_location) 
                ? json_decode($tukang->tukang_location, true)
                : $tukang->tukang_location;
                    
            $tukang->distance = $this->calculateDistance($origin, $tukangLocation);
            return $tukang;
        });

        $tukangTerdekat = $tukangTerdekat->filter(function ($tukang) use ($radius) {
            return $tukang->distance <= $radius;
        });

        if ($tukangTerdekat->isNotEmpty()) {
            $tukangTerdekat = $tukangTerdekat->sortBy('distance')->values();

            return response()->json([
                'status' => 'success',
                'message' => 'Tukang terdekat berhasil ditemukan.',
                'data' => $tukangTerdekat->map(function ($tukang) {
                    return [
                        'tukang_id' => $tukang->id_tukang,
                        'name' => $tukang->name,
                        'rating' => $tukang->rating,
                        'tukang_location' => $tukang->tukang_location,
                        'distance' => $tukang->distance,
                    ];
                }),
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Tidak ada tukang yang berada dalam jangkauan radius.',
        ], 404);
    }

    public function getNearestTukangTarik(Request $request)
    {
        // Ambil user yang terautentikasi
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        // Cari lokasi berdasarkan id_user
        $locate = LocationModel::where('id_user', $user->id_user)->first();

        if (!$locate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lokasi untuk user tidak ditemukan.',
            ], 404);
        }

        $origin = json_decode($locate->origin, true);
        $radius = 2;

        $tukangs = TukangModel::all();

        $tukangTerdekat = $tukangs->map(function ($tukang) use ($origin) {
            // Pastikan decode JSON string ke array
            $tukangLocation = is_string($tukang->tukang_location) 
                ? json_decode($tukang->tukang_location, true)
                : $tukang->tukang_location;
                    
            $tukang->distance = $this->calculateDistance($origin, $tukangLocation);
            return $tukang;
        });

        $tukangTerdekat = $tukangTerdekat->filter(function ($tukang) use ($radius) {
            return $tukang->distance <= $radius;
        });

        if ($tukangTerdekat->isNotEmpty()) {
            $tukangTerdekat = $tukangTerdekat->sortBy('distance')->values();

            return response()->json([
                'status' => 'success',
                'message' => 'Tukang terdekat berhasil ditemukan.',
                'data' => $tukangTerdekat->map(function ($tukang) {
                    return [
                        'tukang_id' => $tukang->id_tukang,
                        'name' => $tukang->name,
                        'rating' => $tukang->rating,
                        'tukang_location' => $tukang->tukang_location,
                        'foto_diri' => $tukang->foto_diri,
                        'spesialisasi' => $tukang->spesialisasi,
                        'distance' => $tukang->distance,
                    ];
                }),
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Tidak ada tukang yang berada dalam jangkauan radius.',
        ], 404);
    }

    // public function getRandomTukang(Request $request)
    // {
    //     // Ambil user yang terautentikasi
    //     $user = auth()->user();

    //     if (!$user) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'User tidak terautentikasi.',
    //         ], 401);
    //     }

    //     // Cari lokasi berdasarkan id_user
    //     $locate = LocationModel::where('id_user', $user->id_user)->first();

    //     if (!$locate) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Lokasi untuk user tidak ditemukan.',
    //         ], 404);
    //     }

    //     $origin = json_decode($locate->origin, true);
    //     $radius = 10; // Radius default 10 km

    //     // Ambil semua tukang dari database
    //     $tukangs = TukangModel::all();

    //     // Hitung jarak dan filter tukang berdasarkan radius
    //     $tukangTerdekat = $tukangs->map(function ($tukang) use ($origin) {
    //         $tukangLocation = is_string($tukang->tukang_location)
    //             ? json_decode($tukang->tukang_location, true)
    //             : $tukang->tukang_location;

    //         $tukang->distance = $this->calculateDistance($origin, $tukangLocation);
    //         return $tukang;
    //     })->filter(function ($tukang) use ($radius) {
    //         return $tukang->distance <= $radius;
    //     })->values(); // Reset index setelah filter

    //     if ($tukangTerdekat->isEmpty()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Tidak ada tukang yang berada dalam jangkauan radius.',
    //         ], 404);
    //     }

    //     // Pilih tukang secara acak
    //     $randomTukang = $tukangTerdekat->random();

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Tukang acak berhasil ditemukan.',
    //         'data' => [
    //             'tukang_id' => $randomTukang->id_tukang,
    //             'name' => $randomTukang->name,
    //             'rating' => $randomTukang->rating,
    //             'tukang_location' => $randomTukang->tukang_location,
    //             'foto_diri' => $randomTukang->foto_diri,
    //             'spesialisasi' => $randomTukang->spesialisasi,
    //             'distance' => $randomTukang->distance,
    //         ],
    //     ], 200);
    // }

    public function postTukangToPesanan(Request $request)
    {
        // Validasi input jenis servis (pastikan dalam bentuk array)
        $jenisServis = $request->input('jenis_servis');
        if (!$jenisServis) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jenis servis tidak ditemukan.',
            ], 400);
        }

        // Ambil user yang terautentikasi
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        // Cari lokasi berdasarkan id_user
        $locate = LocationModel::where('id_user', $user->id_user)->first();
        if (!$locate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lokasi untuk user tidak ditemukan.',
            ], 404);
        }

        $origin = json_decode($locate->origin, true);
        $radius = 10; // Radius default 10 km

        // Ambil semua tukang dari database
        $tukangs = TukangModel::all();

        // Hitung jarak dan filter tukang berdasarkan radius
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

        // Pilih tukang secara acak
        $randomTukang = $tukangTerdekat->random();

        // Tentukan biaya berdasarkan kombinasi jenis servis
        $biaya = DB::table('biaya')
            ->where('jenis_servis', $jenisServis)
            ->first();

        if (!$biaya) {
            return response()->json([
                'status' => 'error',
                'message' => 'Biaya untuk kombinasi servis yang dipilih tidak ditemukan.',
            ], 404);
        }

        // Buat data pesanan
        $pesananData = [
            'id_pesanan' => Str::uuid()->toString(),
            'id_user' => $user->id_user,
            'id_tukang' => $randomTukang->id_tukang,
            'id_biaya' => $biaya->id_biaya,
            'waktu_pesan' => now(),
            'waktu_servis' => now()->addMinutes(30),
            'alamat_servis' => $request->input('alamat_servis', $locate->destination_name),
            'metode_pembayaran' => $request->input('metode_pembayaran', 'Cash'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $biayaInfo = [
            'biaya_servis' => $biaya->biaya_servis,
            'biaya_admin' => $biaya->biaya_admin,
            'biaya_total' => $biaya->biaya_total,
            'jenis_servis' => $biaya->jenis_servis
        ];

        try {
            DB::beginTransaction();
            
            // Simpan ke tabel pesanan
            DB::table('pesanan')->insert($pesananData);
            
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibuat.',
                'data' => [
                    'pesanan' => $pesananData,
                    'biaya' => $biayaInfo,
                    'tukang' => [
                        'id_tukang' => $randomTukang->id_tukang,
                        'distance' => round($randomTukang->distance, 2) . ' km'
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


    public function postTukangToPesananambil(Request $request)
    {
        // Validasi input
        $jenisServis = $request->input('jenis_servis');
        $idTukang = $request->input('id_tukang'); // Tambah input id_tukang

        if (!$jenisServis) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jenis servis tidak ditemukan.',
            ], 400);
        }

        if (!$idTukang) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID Tukang tidak ditemukan.',
            ], 400);
        }

        // Ambil user yang terautentikasi
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        // Cari lokasi berdasarkan id_user
        $locate = LocationModel::where('id_user', $user->id_user)->first();
        if (!$locate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lokasi untuk user tidak ditemukan.',
            ], 404);
        }

        $origin = json_decode($locate->origin, true);
        $radius = 10; // Radius default 10 km

        // Cari tukang berdasarkan id
        $selectedTukang = TukangModel::find($idTukang);
        
        if (!$selectedTukang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tukang tidak ditemukan.',
            ], 404);
        }

        // Hitung jarak tukang yang dipilih
        $tukangLocation = is_string($selectedTukang->tukang_location)
            ? json_decode($selectedTukang->tukang_location, true)
            : $selectedTukang->tukang_location;
        
        $distance = $this->calculateDistance($origin, $tukangLocation);
        
        // Cek apakah tukang dalam radius
        if ($distance > $radius) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tukang berada di luar jangkauan radius ' . $radius . ' km.',
                'distance' => round($distance, 2) . ' km'
            ], 400);
        }

        // Tentukan biaya berdasarkan kombinasi jenis servis
        $biaya = DB::table('biaya')
            ->where('jenis_servis', $jenisServis)
            ->first();

        if (!$biaya) {
            return response()->json([
                'status' => 'error',
                'message' => 'Biaya untuk kombinasi servis yang dipilih tidak ditemukan.',
            ], 404);
        }

        // Buat data pesanan
        $pesananData = [
            'id_pesanan' => Str::uuid()->toString(),
            'id_user' => $user->id_user,
            'id_tukang' => $selectedTukang->id_tukang,
            'id_biaya' => $biaya->id_biaya,
            'waktu_pesan' => now(),
            'waktu_servis' => now()->addMinutes(30),
            'alamat_servis' => $request->input('alamat_servis', $locate->destination_name),
            'metode_pembayaran' => $request->input('metode_pembayaran', 'Cash'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $biayaInfo = [
            'biaya_servis' => $biaya->biaya_servis,
            'biaya_admin' => $biaya->biaya_admin,
            'biaya_total' => $biaya->biaya_total,
            'jenis_servis' => $biaya->jenis_servis
        ];

        try {
            DB::beginTransaction();
            
            // Simpan ke tabel pesanan
            DB::table('pesanan')->insert($pesananData);
            
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibuat.',
                'data' => [
                    'pesanan' => $pesananData,
                    'biaya' => $biayaInfo,
                    'tukang' => [
                        'id_tukang' => $selectedTukang->id_tukang,
                        'distance' => round($distance, 2) . ' km'
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




    // public function getNearestTukang(Request $request, $id_user)
    // {
    //     // Cari lokasi berdasarkan id_user
    //     $locate = LocationModel::where('id_user', $id_user)->first();

    //     if (!$locate) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Lokasi untuk user tidak ditemukan.',
    //         ], 404);
    //     }

    //     $origin = json_decode($locate->origin, true);
    //     $radius = 10; // Radius default 10 km

    //     $tukangs = TukangModel::all();

    //     $tukangTerdekat = $tukangs->map(function ($tukang) use ($origin) {
    //         // Pastikan decode JSON string ke array
    //         $tukangLocation = is_string($tukang->tukang_location) 
    //             ? json_decode($tukang->tukang_location, true)
    //             : $tukang->tukang_location;
                    
    //         $tukang->distance = $this->calculateDistance($origin, $tukangLocation);
    //         return $tukang;
    //     });

    //     $tukangTerdekat = $tukangTerdekat->filter(function ($tukang) use ($radius) {
    //         return $tukang->distance <= $radius;
    //     });

    //     if ($tukangTerdekat->isNotEmpty()) {
    //         $tukangTerdekat = $tukangTerdekat->sortBy('distance')->values();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Tukang terdekat berhasil ditemukan.',
    //             'data' => $tukangTerdekat->map(function ($tukang) {
    //                 return [
    //                     'tukang_id' => $tukang->id_tukang,
    //                     'name' => $tukang->name,
    //                     'rating' => $tukang->rating,
    //                     'tukang_location' => $tukang->tukang_location,
    //                     'distance' => $tukang->distance,
    //                 ];
    //             }),
    //         ], 200);
    //     }

    //     return response()->json([
    //         'status' => 'error',
    //         'message' => 'Tidak ada tukang yang berada dalam jangkauan radius.',
    //     ], 404);
    // }
}
