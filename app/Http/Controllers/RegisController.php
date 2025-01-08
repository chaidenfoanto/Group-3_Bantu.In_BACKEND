<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisController extends Controller
{
    // public function indexUser()
    // {
    //     $user = User::all(); // Ganti user dengan Regis
    //     return response()->json([
    //         'status' => true,
    //         'message' => 'users retrieved successfully',
    //         'data' => $user
    //     ], 200);
    // }

    public function showUser(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'user found successfully',
            'data' => [
                'id_user' => $user->id_user,
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'Tidak ditampilkan secara umum',
                'no_hp' => $user->no_hp,
                'alamat' => $user->alamat,
                'deskripsi_alamat' => $user->deskripsi_alamat,
                'rating' => 0,
                'total_rating' => 0, // Inisialisasi total rating sebagai 0 sebelum diupdate di proses rating
            ]
        ], 200);
    }

    public function registersUser(Request $request)
    {
        // Validasi input request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255', // Sesuaikan dengan nama tabel
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|min:8|same:password', // Sesuaikan dengan nama field password
            'no_hp' => 'required|string|min:11',
            'alamat' => 'required|string',
            'deskripsi_alamat' => 'required|string', // Inisialisasi deskripsi_alamat sebagai null sebelum diupdate di proses rating
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Membuat user dan meng-hash password sebelum menyimpannya
            $user = User::create([
                'id_user' => $request->id_user,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password), // Hash password saat penyimpanan
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'deskripsi_alamat' => $request->deskripsi_alamat, // Inisialisasi deskripsi_alamat sebagai null sebelum diupdate di proses rating
            ]);

            // Kirimkan response tanpa password
            return response()->json([
                'status' => true,
                'message' => 'user created successfully',
                'data' => $user
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function loginUser(Request $request)
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
        $user = User::where('email', $request->email)->first();
        
        // Jika user tidak ditemukan
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Verifikasi password dengan Hash::check
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid password',
            ], 401);
        }

        // Buat token dengan Sanctum
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'access_token' => 'Rahasia',
            'token_type' => 'Bearer',
            'user' => [
                'id_user' => $user->id_user,
                'name' => $user->name,
                'email' => $user->email,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ],
        ], 200);
    }

    public function updateUser(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        // Validasi data input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id_user . ',id_user',
            'no_hp' => 'required|string|min:11',
            'alamat' => 'nullable|string',
            'deskripsi_alamat' => 'nullable|string'
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Persiapkan data untuk update
            $dataToUpdate = $request->except('password');

            // Jika password diberikan, lakukan hash dan update
            if ($request->filled('password')) {
                $dataToUpdate['password'] = Hash::make($request->password);
            }

            // Update user
            $user->update($dataToUpdate);

            // Kembalikan response sukses
            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
                'data' => [
                    'id_user' => $user->id_user,
                    'name' => $user->name,
                    'email' => $user->email,
                    'no_hp' => $user->no_hp,
                    'alamat' => $user->alamat,
                    'deskripsi_alamat' => $user->deskripsi_alamat,
                ]
            ], 200);

        } catch (\Exception $e) {
            // Tangani kesalahan jika terjadi
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(), // Untuk debugging
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        // Validasi data input
        $validator = Validator::make($request->all(), [
            'password' => 'nullable|string|min:8',
            'password_confirmation' => 'required|string|min:8|same:password',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Persiapkan data untuk update
            $dataToUpdate = $request->except('password');

            // Jika password diberikan, lakukan hash dan update
            if ($request->filled('password')) {
                $dataToUpdate['password'] = Hash::make($request->password);
            }

            // Update user
            $user->update($dataToUpdate);

            // Kembalikan response sukses
            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
                'data' => [
                    'id_user' => $user->id_user,
                    'password' => $user->password,
                ]
            ], 200);

        } catch (\Exception $e) {
            // Tangani kesalahan jika terjadi
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(), // Untuk debugging
            ], 500);
        }
    }


    public function destroyUser()
    {
        // Ambil user yang sedang terautentikasi
        $user = auth()->user();

        // Jika user tidak terautentikasi
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        try {
            // Hapus data user yang terautentikasi
            $user->delete();

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

    public function logout()
    {
        Auth::user()->tokens()->delete();
        return response()->json([
            'message' => 'logout success'
        ]);
    }
    
}