<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LocationModel;
use App\Models\TukangModel;
use App\Models\User;
use App\Events\UpdatedLocationTukang;
use App\Events\ChangeTimeService;
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
        UpdatedLocationTukang::dispatch($tukang);

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
                    $data = [
                        'tukang_id' => $tukang->id_tukang,
                        'name' => $tukang->name,
                        'rating' => $tukang->rating,
                        'tukang_location' => $tukang->tukang_location,
                        'foto_diri' => $tukang->foto_diri,
                        'spesialisasi' => $tukang->spesialisasi,
                        'distance' => $tukang->distance,
                    ];

                    return $data;
                }),
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Tidak ada tukang yang berada dalam jangkauan radius.',
        ], 404);
    }


    // public function getNearestTukang(Request $request)
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
    //     $radius = 2;

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

    // public function getNearestTukangTarik(Request $request)
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
    //     $radius = 2;

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
    //                     'foto_diri' => $tukang->foto_diri,
    //                     'spesialisasi' => $tukang->spesialisasi,
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

    public function updateWaktuServis(Request $request)
    {
        $tukang = auth()->user();

        if (!$tukang || !$tukang->id_tukang) {
            return response()->json([
                'status' => 'error',
                'message' => !$tukang ? 'User tidak terautentikasi.' : 'Hanya tukang yang dapat mengubah waktu servis.',
            ], !$tukang ? 401 : 403);
        }

        $tukangLocation = $tukang->tukang_location;
        if (is_string($tukangLocation)) {
            $tukangLocation = json_decode($tukangLocation, true);
        } elseif (!is_array($tukangLocation)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format lokasi tukang tidak valid.',
            ], 400);
        }

        if (!isset($tukangLocation['lat'], $tukangLocation['lng'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format lokasi tukang tidak valid.',
            ], 400);
        }

        // Mengambil daftar pesanan dengan join tabel tukang dan location
        $pesananList = collect(DB::table('pesanan')
            ->join('location', 'pesanan.id_user', '=', 'location.id_user')
            ->join('tukang', 'pesanan.id_tukang', '=', 'tukang.id_tukang')
            ->where('pesanan.id_tukang', $tukang->id_tukang)
            ->whereNull('pesanan.waktu_servis')
            ->select('pesanan.*', 'location.destination', 'tukang.tukang_location')
            ->first());

        if ($pesananList->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada pesanan yang dapat diubah waktu servisnya.',
            ], 404);
        }

        $updatedCount = 0;
        $processedPesanan = [];

        foreach ($pesananList as $pesanan) {
            \Log::info('Processing pesanan:', ['id_pesanan' => $pesanan->id_pesanan]);
            
            $destinationLocation = $pesanan->destination;
            $tukangLocation = $pesanan->tukang_location;

            // Handle double-escaped destination JSON
            if (is_string($destinationLocation)) {
                $destinationLocation = json_decode($destinationLocation, true);
                if (is_string($destinationLocation)) {
                    $destinationLocation = json_decode($destinationLocation, true);
                }
            }

            // Handle tukang location JSON
            if (is_string($tukangLocation)) {
                $tukangLocation = json_decode($tukangLocation, true);
            }

            // Validate both locations
            if (!is_array($destinationLocation) || !isset($destinationLocation['lat'], $destinationLocation['lng'])) {
                \Log::warning('Invalid destination format:', [
                    'id_pesanan' => $pesanan->id_pesanan,
                    'destination' => $destinationLocation
                ]);
                continue;
            }

            if (!is_array($tukangLocation) || !isset($tukangLocation['lat'], $tukangLocation['lng'])) {
                \Log::warning('Invalid tukang location format:', [
                    'id_pesanan' => $pesanan->id_pesanan,
                    'tukang_location' => $tukangLocation
                ]);
                continue;
            }

            // Calculate distance and log coordinates
            $distance = $this->calculateHaversineDistance($destinationLocation, $tukangLocation);
            
            \Log::info('Distance calculation:', [
                'id_pesanan' => $pesanan->id_pesanan,
                'destination' => $destinationLocation,
                'tukang_location' => $tukangLocation,
                'distance' => $distance
            ]);

            // Check if within 100 meters (0.1 km)
            if ($distance <= 0.5) {
                try {
                    DB::beginTransaction();

                    $updated = DB::table('pesanan')
                        ->where('id_pesanan', $pesanan->id_pesanan)
                        ->whereNull('waktu_servis')
                        ->update([
                            'waktu_servis' => now(),
                            'updated_at' => now(),
                        ]);

                    if ($updated) {
                        // Fetch the updated pesanan for the event
                        $updatedPesanan = DB::table('pesanan')
                            ->where('id_pesanan', $pesanan->id_pesanan)
                            ->first();

                        if ($updatedPesanan) {
                            ChangeTimeService::dispatch($updatedPesanan);
                            $updatedCount++;
                            $processedPesanan[] = $updatedPesanan;
                        }
                    }

                    DB::commit();
                    
                    \Log::info('Successfully updated pesanan:', [
                        'id_pesanan' => $pesanan->id_pesanan,
                        'waktu_servis' => now()
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Failed to update waktu_servis:', [
                        'error' => $e->getMessage(),
                        'id_pesanan' => $pesanan->id_pesanan,
                    ]);
                }
            } else {
                \Log::info('Pesanan outside service radius:', [
                    'id_pesanan' => $pesanan->id_pesanan,
                    'distance' => $distance
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Waktu servis diperbarui untuk ' . $updatedCount . ' pesanan dalam radius layanan.',
            'updated_count' => $updatedCount,
            'processed_pesanan' => $processedPesanan,
        ]);
    }

    private function calculateHaversineDistance($origin, $tukangLocation)
    {
        try {
            \Log::info('Calculating distance between:', [
                'origin' => $origin,
                'tukangLocation' => $tukangLocation
            ]);

            if (!isset($origin['lat'], $origin['lng'], $tukangLocation['lat'], $tukangLocation['lng'])) {
                throw new \InvalidArgumentException('Missing coordinates in input locations');
            }

            $lat1 = (float)$origin['lat'];
            $lng1 = (float)$origin['lng'];
            $lat2 = (float)$tukangLocation['lat'];
            $lng2 = (float)$tukangLocation['lng'];

            // Validate coordinates are within reasonable bounds
            if (abs($lat1) > 90 || abs($lat2) > 90 || abs($lng1) > 180 || abs($lng2) > 180) {
                throw new \InvalidArgumentException('Coordinates out of valid range');
            }

            $earthRadius = 6371; // Radius of Earth in kilometers

            $latDifferenceRad = deg2rad($lat2 - $lat1);
            $lngDifferenceRad = deg2rad($lng2 - $lng1);

            $lat1Rad = deg2rad($lat1);
            $lat2Rad = deg2rad($lat2);

            $a = sin($latDifferenceRad / 2) * sin($latDifferenceRad / 2) +
                cos($lat1Rad) * cos($lat2Rad) *
                sin($lngDifferenceRad / 2) * sin($lngDifferenceRad / 2);

            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = $earthRadius * $c;

            \Log::info('Distance calculated:', ['distance' => $distance]);

            return $distance;

        } catch (\Exception $e) {
            \Log::error('Error in calculateHaversineDistance:', [
                'error' => $e->getMessage(),
                'origin' => $origin,
                'tukangLocation' => $tukangLocation
            ]);
            return PHP_FLOAT_MAX;
        }
    }

    public function updateWaktuServispencet(Request $request)
    {
        $tukang = auth()->user();

        if (!$tukang || !$tukang->id_tukang) {
            return response()->json([
                'status' => 'error',
                'message' => !$tukang ? 'User tidak terautentikasi.' : 'Hanya tukang yang dapat mengubah waktu servis.',
            ], !$tukang ? 401 : 403);
        }

        $idPesanan = $request->input('id_pesanan');

        $Pesanan = DB::table('pesanan')
            ->where('id_pesanan', $idPesanan)
            ->first();

        if (!$Pesanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        DB::table('pesanan')
            ->where('id_pesanan', $idPesanan)
            ->update([
                'waktu_servis' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tukang selesai bekerja',
        ], 200);
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
        // Validasi input dengan kondisional untuk id_tukang
        $validationRules = [
            'jenis_servis' => 'required|array',
            'jenis_servis.*.jenis' => 'required|string|exists:biaya,jenis_servis',
            'jenis_servis.*.kuantitas' => 'required|integer|min:1',
            'alamat_servis' => 'required|string',
            'metode_pembayaran' => 'required|in:Cash,Non-tunai'
        ];

        // Tambahkan validasi id_tukang jika ada
        if ($request->has('id_tukang')) {
            $validationRules['id_tukang'] = 'required|exists:tukang,id_tukang';
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
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

        // Logic untuk mencari tukang
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

        // Hitung total biaya dan siapkan detail pesanan
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

    // public function postTukangToPesanan(Request $request)
    // {
    //     // Validasi input
    //     $validator = Validator::make($request->all(), [
    //         'jenis_servis' => 'required|array',
    //         'jenis_servis.*.jenis' => 'required|string|exists:biaya,jenis_servis',
    //         'jenis_servis.*.kuantitas' => 'required|integer|min:1',
    //         'alamat_servis' => 'required|string',
    //         'metode_pembayaran' => 'required|in:Cash,Non-tunai'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $validator->errors(),
    //         ], 400);
    //     }

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

    //     // Logic untuk mencari tukang terdekat
    //     $origin = json_decode($locate->origin, true);
    //     $radius = 10;
    //     $tukangs = TukangModel::all();
        
    //     $tukangTerdekat = $tukangs->map(function ($tukang) use ($origin) {
    //         $tukangLocation = is_string($tukang->tukang_location)
    //             ? json_decode($tukang->tukang_location, true)
    //             : $tukang->tukang_location;
    //         $tukang->distance = $this->calculateDistance($origin, $tukangLocation);
    //         return $tukang;
    //     })->filter(function ($tukang) use ($radius) {
    //         return $tukang->distance <= $radius;
    //     })->values();

    //     if ($tukangTerdekat->isEmpty()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Tidak ada tukang yang berada dalam jangkauan radius.',
    //         ], 404);
    //     }

    //     $randomTukang = $tukangTerdekat->random();
        
    //     // Hitung total biaya dan siapkan detail pesanan
    //     $totalBiaya = 0;
    //     $biayaAdmin = 1000;
    //     $detailPesananData = [];
    //     $latestBiaya = null;
        
    //     foreach ($request->jenis_servis as $servis) {
    //         $biaya = DB::table('biaya')
    //             ->where('jenis_servis', $servis['jenis'])
    //             ->first();

    //         if (!$biaya) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Biaya untuk layanan "' . $servis['jenis'] . '" tidak ditemukan.',
    //             ], 404);
    //         }

    //         $subtotal = $biaya->biaya_servis * $servis['kuantitas'];
    //         $totalBiaya += $subtotal;
    //         $biayaAdmin = $biaya->biaya_admin; // Biaya admin hanya sekali
    //         $latestBiaya = $biaya; // Simpan biaya terakhir untuk referensi id_biaya

    //         $detailPesananData[] = [
    //             'nama_layanan' => $servis['jenis'],
    //             'harga_layanan' => $biaya->biaya_servis,
    //             'subtotal' => $subtotal,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ];
    //     }

    //     $totalBiaya += $biayaAdmin; // Tambahkan biaya admin sekali di akhir

    //     // Buat data pesanan utama
    //     $idPesanan = Str::uuid()->toString();
    //     $pesananData = [
    //         'id_pesanan' => $idPesanan,
    //         'id_user' => $user->id_user,
    //         'id_tukang' => $randomTukang->id_tukang,
    //         'id_biaya' => $latestBiaya->id_biaya,
    //         'waktu_pesan' => now(),
    //         'alamat_servis' => $request->alamat_servis,
    //         'metode_pembayaran' => $request->metode_pembayaran,
    //         'kuantitas' => collect($request->jenis_servis)->sum('kuantitas'),
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ];

    //     try {
    //         DB::beginTransaction();
            
    //         // Simpan pesanan utama
    //         DB::table('pesanan')->insert($pesananData);

    //         // Simpan detail pesanan
    //         foreach ($detailPesananData as $detail) {
    //             DB::table('detail_pesanan')->insert([
    //                 'id_pesanan' => $idPesanan,
    //                 'nama_layanan' => $detail['nama_layanan'],
    //                 'harga_layanan' => $detail['harga_layanan'],
    //                 'subtotal' => $detail['subtotal'],
    //                 'created_at' => $detail['created_at'],
    //                 'updated_at' => $detail['updated_at'],
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Pesanan berhasil dibuat.',
    //             'data' => [
    //                 'pesanan' => $pesananData,
    //                 'detail_pesanan' => $detailPesananData,
    //                 'total_biaya' => $totalBiaya,
    //                 'biaya_admin' => $biayaAdmin,
    //                 'tukang' => [
    //                     'id_tukang' => $randomTukang->id_tukang,
    //                     'distance' => round($randomTukang->distance, 2) . ' km'
    //                 ]
    //             ]
    //         ], 201);

    //     } catch (\Exception $e) {
    //         DB::rollback();
            
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan saat membuat pesanan.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }



    // public function postTukangToPesananambil(Request $request)
    // {
    //     // Validasi input
    //     $validator = Validator::make($request->all(), [
    //         'jenis_servis' => 'required|array',
    //         'jenis_servis.*.jenis' => 'required|string|exists:biaya,jenis_servis',
    //         'jenis_servis.*.kuantitas' => 'required|integer|min:1',
    //         'alamat_servis' => 'required|string',
    //         'metode_pembayaran' => 'required|in:Cash,Non-tunai',
    //         'id_tukang' => 'required|exists:tukang,id_tukang'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $validator->errors(),
    //         ], 400);
    //     }

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

    //     // Cek tukang dan jarak
    //     $origin = json_decode($locate->origin, true);
    //     $radius = 10;
    //     $selectedTukang = TukangModel::find($request->id_tukang);
        
    //     if (!$selectedTukang) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Tukang tidak ditemukan.',
    //         ], 404);
    //     }

    //     $tukangLocation = is_string($selectedTukang->tukang_location)
    //         ? json_decode($selectedTukang->tukang_location, true)
    //         : $selectedTukang->tukang_location;
        
    //     $distance = $this->calculateDistance($origin, $tukangLocation);
        
    //     if ($distance > $radius) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Tukang berada di luar jangkauan radius ' . $radius . ' km.',
    //         ], 400);
    //     }

    //     // Hitung total biaya dan siapkan detail pesanan
    //     $totalBiaya = 0;
    //     $biayaAdmin = 1000;
    //     $detailPesananData = [];
    //     $latestBiaya = null;

    //     foreach ($request->jenis_servis as $servis) {
    //         $biaya = DB::table('biaya')
    //             ->where('jenis_servis', $servis['jenis'])
    //             ->first();

    //         if (!$biaya) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Biaya untuk layanan "' . $servis['jenis'] . '" tidak ditemukan.',
    //             ], 404);
    //         }

    //         $subtotal = $biaya->biaya_servis * $servis['kuantitas'];
    //         $totalBiaya += $subtotal;
    //         $biayaAdmin = $biaya->biaya_admin;
    //         $latestBiaya = $biaya;

    //         $detailPesananData[] = [
    //             'nama_layanan' => $servis['jenis'],
    //             'harga_layanan' => $biaya->biaya_servis,
    //             'subtotal' => $subtotal,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ];
    //     }

    //     $totalBiaya += $biayaAdmin;

    //     // Buat data pesanan utama
    //     $idPesanan = Str::uuid()->toString();
    //     $pesananData = [
    //         'id_pesanan' => $idPesanan,
    //         'id_user' => $user->id_user,
    //         'id_tukang' => $selectedTukang->id_tukang,
    //         'id_biaya' => $latestBiaya->id_biaya,
    //         'waktu_pesan' => now(),
    //         'alamat_servis' => $request->alamat_servis,
    //         'metode_pembayaran' => $request->metode_pembayaran,
    //         'kuantitas' => collect($request->jenis_servis)->sum('kuantitas'),
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ];

    //     try {
    //         DB::beginTransaction();
            
    //         // Simpan pesanan utama
    //         DB::table('pesanan')->insert($pesananData);
            
    //         // Simpan detail pesanan
    //         foreach ($detailPesananData as $detail) {
    //             DB::table('detail_pesanan')->insert([
    //                 'id_pesanan' => $idPesanan,
    //                 'nama_layanan' => $detail['nama_layanan'],
    //                 'harga_layanan' => $detail['harga_layanan'],
    //                 'subtotal' => $detail['subtotal'],
    //                 'created_at' => $detail['created_at'],
    //                 'updated_at' => $detail['updated_at'],
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Pesanan berhasil dibuat.',
    //             'data' => [
    //                 'pesanan' => $pesananData,
    //                 'detail_pesanan' => $detailPesananData,
    //                 'total_biaya' => $totalBiaya,
    //                 'biaya_admin' => $biayaAdmin,
    //                 'tukang' => [
    //                     'id_tukang' => $selectedTukang->id_tukang,
    //                     'distance' => round($distance, 2) . ' km'
    //                 ]
    //             ]
    //         ], 201);

    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan saat membuat pesanan.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

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
