<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LocationModel;
use App\Models\TukangModel;
use App\Events\UpdatedLocationTukang;
use App\Events\StartLocationTukang;
use App\Events\EndLocationTukang;

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
    public function getTukangLocation($id_user)
    {
        try {
            // Ambil lokasi berdasarkan id_user
            $location = LocationModel::where('id_user', $id_user)
                ->first();

            if (!$location) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data lokasi tidak ditemukan.',
                ], 404);
            }

            // Hitung tukang terdekat berdasarkan origin dan destination
            $tukangTerdekat = TukangModel::select('*')
                ->get()
                ->map(function ($tukang) use ($location) {
                    $tukang->distance = $this->calculateDistance(
                        json_decode($location->origin, true),
                        json_decode($tukang->location, true)
                    );
                    return $tukang;
                })
                ->sortBy('distance')
                ->first();

            if (!$tukangTerdekat) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tukang terdekat tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'tukang_location' => $tukangTerdekat->location,
                    'distance' => $tukangTerdekat->distance,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menghitung jarak antara dua titik lokasi menggunakan formula haversine.
     *
     * @param array $point1
     * @param array $point2
     * @return float
     */
    private function calculateDistance(array $point1, array $point2)
    {
        $earthRadius = 6371; // Radius bumi dalam kilometer

        $lat1 = deg2rad($point1['lat']);
        $lon1 = deg2rad($point1['lng']);
        $lat2 = deg2rad($point2['lat']);
        $lon2 = deg2rad($point2['lng']);

        $latDiff = $lat2 - $lat1;
        $lonDiff = $lon2 - $lon1;

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos($lat1) * cos($lat2) *
             sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
    

    public function start(Request $request, LocationModel $locate)
    {
        if ($locate->is_started) {
            return response()->json(['message' => 'This work has already started.'], 400);
        }

        if (!$locate->id_tukang) {
            return response()->json(['message' => 'No tukang assigned to this trip.'], 400);
        }

        $locate->update(['is_started' => true]);

        StartLocationTukang::dispatch($locate, $request->user());

        return response()->json([
            'message' => 'The trip has started.',
            'locate' => $locate->load('tukang.user')
        ], 200);
    }

    /**
     * Tandai perjalanan telah selesai oleh tukang.
     */
    public function end(Request $request, LocationModel $locate)
    {
        if (!$locate->is_started) {
            return response()->json(['message' => 'Work has not started yet.'], 400);
        }

        if ($locate->is_completed) {
            return response()->json(['message' => 'Work is already completed.'], 400);
        }

        $locate->update(['is_completed' => true]);

        EndLocationTukang::dispatch($locate, $request->user());

        return response()->json([
            'message' => 'The trip has ended successfully.',
            'locate' => $locate->load('tukang.user')
        ], 200);
    }

    /**
     * Perbarui lokasi tukang saat perjalanan berlangsung.
     */
    public function updateLocation(Request $request, LocationModel $locate)
    {
        if (!$locate->is_started) {
            return response()->json(['message' => 'The trip has not started yet.'], 400);
        }

        if ($locate->is_completed) {
            return response()->json(['message' => 'The trip is already completed.'], 400);
        }

        $request->validate([
            'tukang_location' => 'required|array',
            'tukang_location.lat' => 'required|numeric',
            'tukang_location.lng' => 'required|numeric',
        ]);

        $locate->update(['tukang_location' => $request->tukang_location]);

        UpdateLocationTukang::dispatch($locate, $request->user());

        return response()->json([
            'message' => 'Tukang location updated.',
            'locate' => $locate->load('tukang.user')
        ], 200);
    }
}
