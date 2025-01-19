<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HistoryModel;
use App\Models\LocationModel;
use App\Models\TukangModel;
use App\Models\PesananModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TukangUpdateHistory extends Controller
{
    public function tukangput(Request $request) {
        $tukang = auth()->user();
    
        if (!$tukang) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }
    
        // Validasi input
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
            'id_history' => 'required|exists:history,id_history',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 400);
        }

        $idHistory = $request->input('id_history');
        $Status = $request->input('status');

        $History = DB::table('history')
            ->where('id_history', $idHistory)
            ->first();
    
        if (!$History) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        DB::table('history')
            ->where('id_history', $idHistory)
            ->update([
                'status' => $Status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tukang selesai bekerja',
        ], 200);
    }

    public function tukangtampilkanpesanan(Request $request) {
        $tukang = auth()->user();

        if (!$tukang) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        $pesanan = HistoryModel::whereHas('pesanan', function ($query) use ($tukang) {
            $query->where('id_tukang', $tukang->id_tukang);
            })->with('pesanan')->get();
    
        // Jika tidak ada pesanan ditemukan
        if ($pesanan->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada pesanan yang terkait dengan tukang ini.',
            ], 404);
        }
    
        // Jika data pesanan ditemukan
        return response()->json([
            'status' => 'success',
            'message' => 'Pesanan ditemukan.',
            'data' => $pesanan,
        ], 200);
    }
}