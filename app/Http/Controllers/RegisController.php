<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Regis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class RegisController extends Controller
{
    public function index()
    {
        $customer = Regis::all(); // Ganti Customer dengan Regis
        return response()->json([
            'status' => true,
            'message' => 'Customers retrieved successfully',
            'data' => $customer
        ], 200);
    }

    public function show($id)
    {
        $customer = Regis::findOrFail($id); // Ganti Customer dengan Regis
        return response()->json([
            'status' => true,
            'message' => 'Customer found successfully',
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'password' => 'Tidak ditampilkan secara umum'
            ]
        ], 200);
    }

    public function registers(Request $request)
    {
        // Validasi input request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:regiss|max:255', // Sesuaikan dengan nama tabel
            'password' => 'required|string|min:8',
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
        $customer = Regis::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hash password saat penyimpanan
        ]);

        // Kirimkan response tanpa password
        return response()->json([
            'status' => true,
            'message' => 'Customer created successfully',
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ]
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:regiss,email,' . $id, // Sesuaikan dengan nama tabel
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = Regis::findOrFail($id); 
        $customer->update($request->except('password'));
        
        if ($request->filled('password')) {
            $customer->password = Hash::make($request->password);
            $customer->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Customer updated successfully',
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ]
        ], 200);
    }

    public function destroy($id)
    {
        $customer = Regis::findOrFail($id); // Ganti Customer dengan Regis
        $customer->delete();
        
        return response()->json([
            'status' => true,
            'message' => 'Customer deleted successfully'
        ], 204);
    }
}