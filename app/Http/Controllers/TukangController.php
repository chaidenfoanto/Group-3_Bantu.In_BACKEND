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

    public function showTukang(Request $request)
    {
        $tukang = auth()->user();

        if (!$tukang) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'tukang found successfully',
            'data' => $tukang
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

    public function updateTukang(Request $request)
    {
        // Ambil tukang yang terautentikasi
        $tukang = auth()->user();

        if (!$tukang) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        // Validasi data input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tukang,email,' . $tukang->id_tukang  . ',id_tukang', // Sesuaikan dengan nama tabel
            'password' => 'nullable|string|min:8',
            'no_hp' => 'required|string|min:11',
            'spesialisasi' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update data tukang (kecuali password)
        $tukang->update($request->except('password', 'foto_diri'));

        // Jika ada password baru, hash dan simpan
        if ($request->filled('password')) {
            $tukang->password = Hash::make($request->password);
        }

        // Jika ada foto_diri baru, simpan di storage dan simpan path-nya
        if ($request->hasFile('foto_diri')) {
            // Simpan foto di storage dan dapatkan path-nya
            $path = $request->file('foto_diri')->store('public/foto_tukang');

            // Update path foto diri di database
            $tukang->foto_diri = Storage::url($path);
        }

        // Simpan perubahan ke database
        $tukang->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Tukang updated successfully',
            'data' => [
                'id_tukang' => $tukang->id_tukang,
                'name' => $tukang->name,
                'email' => $tukang->email,
                'password' => 'Tidak ditampilkan secara umum',
                'no_hp' => $tukang->no_hp,
                'spesialisasi' => $tukang->spesialisasi,
            ]
        ], 200);
    }


    public function destroyTukang(Request $request)
    {
        // Ambil user yang sedang terautentikasi
        $tukang = auth()->user();

        // Jika user tidak terautentikasi
        if (!$tukang) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        try {
            // Hapus data user yang terautentikasi
            $tukang->delete();

            return response()->json([
                'status' => true,
                'message' => 'User deleted successfully.'
            ], 200); // Gunakan status 200 untuk memberikan konfirmasi bahwa permintaan berhasil
        } catch (\Exception $e) {
            // Tangani kesalahan jika terjadi masalah saat penghapusan
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting the user.',
                'error' => $e->getMessage(),
            ], 500);
        }
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
