<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TukangUpdateHistory extends Controller
{
    public function tukangput() {
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

        $History = DB::table('history')->where('id_history', $idHistory)->first();
    
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
}
