<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\TukangModel;
use App\Models\RatingTukangModel;
use Carbon\Carbon;

class CommentsTukangController extends Controller
{
    public function kasihratinguser(Request $request, $id_tukang) {
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
                'message' => 'Hanya tukang yang dapat memberi rating.',
            ], 403); 
        }
    
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|between:0,5',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
    
        // Cek apakah rating sudah ada
        $rating = RatingTukangModel::where('id_tukang', $id_tukang)
            ->where('id_user', $user->id_user)
            ->first();
    
        if ($rating) {
            // Update rating jika data sudah ada
            $rating->rating = $request->rating;
            $rating->save();
        } else {
            // Buat data baru jika rating belum ada
            $rating = RatingTukangModel::create([
                'id_tukang' => $id_tukang,
                'id_user' => $user->id_user,
                'rating' => $request->rating,
            ]);
        }
    
        // Hitung rata-rata rating baru
        $averageRating = RatingTukangModel::where('id_tukang', $id_tukang)->avg('rating');
    
        // Simpan rata-rata ke tabel User atau model terkait
        TukangModel::where('id_tukang', $id_tukang)->update(['total_rating' => $averageRating]);
    
        return response()->json([
            'status' => true,
            'message' => 'Rating berhasil disimpan dan rata-rata diperbarui.',
            'data' => $rating
        ], 201);
    }
    
    public function kasihulasanuser(Request $request, $id_tukang)
    {
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
                'message' => 'Hanya user yang dapat memberi ulasan.',
            ], 403); 
        }

        $validator = Validator::make($request->all(), [
            'ulasan' => 'required|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek apakah rating sudah ada
        $rating = RatingTukangModel::where('id_tukang', $id_tukang)
            ->where('id_user', $user->id_user)
            ->first();

        if ($rating) {
            if ($rating->ulasan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ulasan sudah ada dan tidak bisa diubah.',
                ], 403);
            }

            // Update ulasan jika rating sudah ada tetapi ulasan belum ada
            $rating->ulasan = $request->ulasan;
            $rating->tanggal_rating = Carbon::now();
            $rating->save();

            return response()->json([
                'status' => true,
                'message' => 'Ulasan berhasil diperbarui.',
                'data' => $rating
            ], 200);
        } else {
            // Menolak jika data tidak ditemukan
            return response()->json([
                'status' => 'error',
                'message' => 'Data rating tidak ditemukan. Pastikan untuk memberikan rating terlebih dahulu.',
            ], 404);
        }
    }

    public function getKomentarTukang($id_tukang)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        // Ambil semua rating dan ulasan berdasarkan id_user
        $ratings = RatingTukangModel::where('id_tukang', $id_tukang)
            ->with(['user:id_user,name']) // Relasi ke model TukangModel
            ->get();

        if ($ratings->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Belum ada rating atau ulasan untuk user ini.',
            ], 404);
        }

        // Format response
        $response = $ratings->map(function ($rating) {
            return [
                'id_user' => $rating->id_user,
                'user_name' => $rating->user->name,
                'user_foto' => $rating->user->foto_diri ? 'data:image/png;base64,' . base64_encode($rating->user->foto_diri) : null,
                'rating' => $rating->rating,
                'ulasan' => $rating->ulasan,
                'tanggal_rating' => $rating->tanggal_rating,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Data rating dan ulasan berhasil diambil.',
            'data' => $response,
        ], 200);
    }
}
