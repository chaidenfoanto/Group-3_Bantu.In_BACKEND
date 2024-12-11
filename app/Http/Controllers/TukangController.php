<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TukangModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class TukangController extends Controller
{
    public function indexTukang()
    {
        $tukang = TukangModel::all(); // Ganti tukang dengan Regis
        return response()->json([
            'status' => true,
            'message' => 'tukangs retrieved successfully',
            'data' => $tukang
        ], 200);
    }

    public function showTukang($id_tukang)
    {
        $tukang = TukangModel::findOrFail($id_tukang); // Ganti tukang dengan Regis
        return response()->json([
            'status' => true,
            'message' => 'tukang found successfully',
            'data' => [
                'id_tukang' => $tukang->id_tukang,
                'name' => $tukang->name,
                'email' => $tukang->email,
                'password' => 'Tidak ditampilkan secara umum'
            ]
        ], 200);
    }

    public function registersTukang(Request $request)
    {
        // Validasi input request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:tukang|max:255', // Sesuaikan dengan nama tabel
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|min:8|same:password', // Sesuaikan dengan nama field password
            'no_hp' => 'required|string|min:11',
            'spesialisasi' => 'required|string',
            'ktp' => 'required|file|mimes:jpeg,png,jpg', // Sesuaikan dengan nama field ktp
            'foto_diri' => 'required|file|mimes:jpeg,png,jpg'
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Membuat tukang dan meng-hash password sebelum menyimpannya
        $tukang = TukangModel::create([
            'id_tukang' => $request->id_tukang,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hash password saat penyimpanan
            'no_hp' => $request->no_hp,
            'spesialisasi' => $request->spesialisasi,
            'ktp' => $request->file('ktp')->store('public/ktp'), // Simpan file ktp di folder public/ktp
            'foto_diri' => $request->file('foto_diri')->store('public/foto_diri'), //
        ]);

        $token = $tukang->createToken('authToken')->plainTextToken;

        // Kirimkan response tanpa password
        return response()->json([
            'status' => true,
            'message' => 'tukang created successfully',
            'data' => [
                'id_tukang' => $tukang->id_tukang,
                'name' => $tukang->name,
                'email' => $tukang->email,
                'password' => 'Tidak ditampilkan secara umum',
                'no_hp' => $tukang->no_hp,
                'spesialisasi' => $tukang->spesialisasi,
                'ktp' => $tukang->ktp, // Ditampilkan URL file ktp,
                'foto_diri' => $tukang->foto_diri, // Ditampilkan URL file foto diri,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 201);
    }

    public function loginTukang(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Ambil kredensial
        $credentials = $request->only('email', 'password');
        
        // Cek apakah email ada di database
        $tukang = TukangModel::where('email', $request->email)->first();
        
        // Jika tukang tidak ditemukan
        if (!$tukang) {
            return response()->json([
                'status' => false,
                'message' => 'tukang not found',
            ], 404);
        }

        // Verifikasi password dengan Hash::check
        if (!Hash::check($request->password, $tukang->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid password',
            ], 401);
        }

        // Buat token dengan Sanctum
        $token = $tukang->createToken('authToken')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'access_token' => 'Rahasia',
            'token_type' => 'Bearer',
            'tukang' => [
                'id_tukang' => $tukang->id_tukang,
                'name' => $tukang->name,
                'email' => $tukang->email,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ],
        ], 200);
    }

    public function updateTukang(Request $request, $id_tukang)
    {
        $tukang = TukangModel::where('id_tukang', $id_tukang)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tukang,email,' . $id_tukang  . ',id_tukang', // Sesuaikan dengan nama tabel
            'password' => 'nullable|string|min:8',
            'no_hp' => 'required|string|min:11',
            'spesialisasi' => 'required|string',
            'foto_diri' => 'nullable|file|mimes:jpeg,png,jpg|max:2048', // Sesuaikan dengan nama field ktp
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $tukang->update($request->except('password'));
        
        if ($request->filled('password')) {
            $tukang->password = Hash::make($request->password);
            $tukang->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'tukang updated successfully',
            'data' => [
                'id_tukang' => $tukang->id_tukang,
                'name' => $tukang->name,
                'email' => $tukang->email,
                'password' => 'Tidak ditampilkan secara umum',
                'no_hp' => $tukang->no_hp,
                'spesialisasi' => $tukang->spesialisasi,
                'foto_diri' => $tukang->foto_diri // Ditampilkan URL file foto diri,
            ]
        ], 200);
    }

    public function destroyTukang($id_tukang)
    {
        $tukang = TukangModel::findOrFail($id_tukang); // Ganti tukang dengan Regis
        $tukang->delete();
        
        return response()->json([
            'status' => true,
            'message' => 'tukang deleted successfully'
        ], 204);
    }
    public function logout(Request $request)
    {
        // Hapus token autentikasi Tukang
        $request->user('tukang')->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful for Tukang',
        ], 200);
        
    }
}
