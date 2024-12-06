<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TukangModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class TukangController extends Controller
{
    public function indexTukang()
    {
        $customer = TukangModel::all(); // Ganti Customer dengan Regis
        return response()->json([
            'status' => true,
            'message' => 'Customers retrieved successfully',
            'data' => $customer
        ], 200);
    }

    public function showTukang($id_tukang)
    {
        $customer = TukangModel::findOrFail($id_tukang); // Ganti Customer dengan Regis
        return response()->json([
            'status' => true,
            'message' => 'Customer found successfully',
            'data' => [
                'id_tukang' => $customer->id_tukang,
                'name' => $customer->name,
                'email' => $customer->email,
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
            'password_confirmation' => 'required|string|min:8|same:password' // Sesuaikan dengan nama field password
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Membuat customer dan meng-hash password sebelum menyimpannya
        $customer = TukangModel::create([
            'id_tukang' => $request->id_tukang,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hash password saat penyimpanan
        ]);

        // Kirimkan response tanpa password
        return response()->json([
            'status' => true,
            'message' => 'Customer created successfully',
            'data' => [
                'id_tukang' => $customer->id_tukang,
                'name' => $customer->name,
                'email' => $customer->email,
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
        $user = TukangModel::where('email', $request->email)->first();
        
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
                'id_tukang' => $user->id_tukang,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    public function updateTukang(Request $request, $id_tukang)
    {
        $customer = TukangModel::where('id_tukang', $id_tukang)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tukang,email,' . $id_tukang  . ',id_tukang', // Sesuaikan dengan nama tabel
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer->update($request->except('password'));
        
        if ($request->filled('password')) {
            $customer->password = Hash::make($request->password);
            $customer->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Customer updated successfully',
            'data' => [
                'id_tukang' => $customer->id_tukang,
                'name' => $customer->name,
                'email' => $customer->email,
            ]
        ], 200);
    }

    public function destroyTukang($id_tukang)
    {
        $customer = TukangModel::findOrFail($id_tukang); // Ganti Customer dengan Regis
        $customer->delete();
        
        return response()->json([
            'status' => true,
            'message' => 'Customer deleted successfully'
        ], 204);
    }
}
