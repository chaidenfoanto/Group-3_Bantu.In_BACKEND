<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LocationModel;
use App\Models\TukangModel;
use App\Models\DetailPesananModel;
use App\Models\User;
use App\Events\UpdatedLocationTukang;
use App\Events\StartLocationTukang;
use App\Events\EndLocationTukang;
use Illuminate\Support\Facades\Validator;
use App\Models\PesananModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DetailPesananController extends Controller
{
    public function getDetailPesanan(Request $request) {
        $idDetail = $request->input('id_pesanan');
    
        if (!$idDetail) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID Tukang tidak ditemukan.',
            ], 400);
        }
    
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }
    
        $detil = DetailPesananModel::where('id_pesanan', $idDetail)->get();
    
        if ($detil->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lokasi untuk user tidak ditemukan.',
            ], 404);
        }
    
        // Gabungkan data berdasarkan id_pesanan
        $result = $detil->groupBy('id_pesanan')->map(function ($items, $id_pesanan) {
            $totalSubtotal = $items->sum('subtotal');
            $biayaAdmin = 1000; // Tetapkan biaya admin
            return [
                'id_pesanan' => $id_pesanan,
                'subtotal' => $totalSubtotal + $biayaAdmin, // Tambahkan biaya admin ke subtotal
                'biaya_admin' => $biayaAdmin,
                'detail_layanan' => $items->map(function ($item) {
                    return [
                        'id_detailpesanan' => $item->id_detailpesanan,
                        'nama_layanan' => $item->nama_layanan,
                        'harga_layanan' => $item->harga_layanan,
                        'subtotal' => $item->subtotal,
                        'deskripsi_servis' => $item->deskripsi_servis,
                    ];
                }),
            ];
        })->values();
    
        return response()->json([
            'status' => 'success',
            'data' => $result,
        ], 200);
    }
    
    public function putDetailPesanandeskripsiservis(Request $request) {
        // Ambil user yang terautentikasi
        $user = auth()->user();
    
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }
    
        // Validasi input
        $validator = Validator::make($request->all(), [
            'deskripsi_servis' => 'required|string|max:255',
            'id_pesanan' => 'required|exists:pesanan,id_pesanan',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 400);
        }
    
        // Ambil ID pesanan dari input
        $idPesanan = $request->input('id_pesanan');
        $deskripsiServis = $request->input('deskripsi_servis');
    
        // Cari pesanan berdasarkan id_pesanan yang diberikan
        $pesanan = DB::table('pesanan')->where('id_pesanan', $idPesanan)->first();
    
        if (!$pesanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }
    
        // Update deskripsi_servis pada detail pesanan yang terkait dengan id_pesanan
        try {
            DB::table('detail_pesanan')
                ->where('id_pesanan', $idPesanan)
                ->update([
                    'deskripsi_servis' => $deskripsiServis,
                    'updated_at' => now(),
                ]);
    
            return response()->json([
                'status' => 'success',
                'message' => 'Deskripsi servis berhasil diperbarui.',
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui deskripsi servis.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function getDetailAkhir(Request $request, $idPesanan) {
        // Ambil user yang terautentikasi
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }
    
        // Ambil data detail pesanan berdasarkan id_pesanan
        $detailPesanan = DetailPesananModel::join('pesanan', 'pesanan.id_pesanan', '=', 'detail_pesanan.id_pesanan')
            ->leftJoin('users', 'pesanan.id_user', '=', 'users.id_user') // Join dengan tabel users untuk mendapatkan data user
            ->leftJoin('tukang', 'pesanan.id_tukang', '=', 'tukang.id_tukang') // Join dengan tabel tukang untuk mendapatkan data tukang
            ->where('detail_pesanan.id_pesanan', $idPesanan)
            ->get([
                'detail_pesanan.*', 
                'pesanan.kuantitas',
                'pesanan.id_pesanan',
                'pesanan.metode_pembayaran',
                'users.no_hp',        // Menambahkan no_hp dari tabel users
                'users.name as user_name',  // Menambahkan name dari tabel users sebagai user_name
                'users.alamat',       // Menambahkan alamat dari tabel users
                'tukang.name as tukang_name' // Menambahkan name dari tabel tukang sebagai tukang_name
            ]);
    
        if ($detailPesanan->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Detail pesanan tidak ditemukan untuk ID pesanan: ' . $idPesanan,
            ], 404);
        }
    
        // Gabungkan nama_layanan menjadi satu string
        $namaLayananGabungan = $detailPesanan->pluck('nama_layanan')->implode(' & ');
        $hargaLayananGabung = $detailPesanan->pluck('harga_layanan')->implode(' & ');
    
        // Ambil kuantitas untuk masing-masing layanan
        $kuantitasLayanan = $detailPesanan->mapWithKeys(function ($item) {
            return [$item->nama_layanan => $item->kuantitas];
        });
    
        // Ambil deskripsi_servis, id_detailpesanan, metode_pembayaran dan created_at untuk salah satu layanan
        $firstDetailPesanan = $detailPesanan->first(); // Ambil detail pesanan pertama
        $deskripsiServis = $firstDetailPesanan ? $firstDetailPesanan->deskripsi_servis : null;
        $idDetailPesanan = $firstDetailPesanan ? $firstDetailPesanan->id_detailpesanan : null;
        $createdAt = $firstDetailPesanan ? $firstDetailPesanan->created_at : null;
        $idPesananFromPesanan = $firstDetailPesanan ? $firstDetailPesanan->id_pesanan : null;
        $metodePembayaran = $firstDetailPesanan ? $firstDetailPesanan->metode_pembayaran : null;
        $noHpUser = $firstDetailPesanan ? $firstDetailPesanan->no_hp : null; // Menambahkan no_hp dari users
        $userName = $firstDetailPesanan ? $firstDetailPesanan->user_name : null; // Menambahkan name dari users
        $alamatUser = $firstDetailPesanan ? $firstDetailPesanan->alamat : null; // Menambahkan alamat dari users
        $tukangName = $firstDetailPesanan ? $firstDetailPesanan->tukang_name : null; // Menambahkan name dari tukang
    
        // Hitung subtotal masing-masing layanan
        $subtotal = $detailPesanan->sum('subtotal'); // Jumlahkan semua subtotal
    
        // Biaya admin
        $biayaAdmin = 1000;
    
        // Hitung total harga
        $totalHarga = $subtotal + $biayaAdmin;
    
        // Kembalikan response
        return response()->json([
            'status' => 'success',
            'message' => 'Detail pesanan ditemukan.',
            'data' => [
                'nama_layanan' => $namaLayananGabungan,
                'harga_layanan' => $hargaLayananGabung,
                'kuantitas_layanan' => $kuantitasLayanan, // Kuantitas masing-masing layanan
                'deskripsi_servis' => $deskripsiServis,
                'id_detailpesanan' => $idDetailPesanan,
                'id_pesanan' => $idPesananFromPesanan,
                'metode_pembayaran' => $metodePembayaran,
                'created_at' => $createdAt,
                'no_hp_user' => $noHpUser, // No. HP user
                'user_name' => $userName, // Nama user
                'alamat_user' => $alamatUser, // Alamat user
                'tukang_name' => $tukangName, // Nama tukang
                'subtotal' => $subtotal,
                'biaya_admin' => $biayaAdmin,
                'total_harga' => $totalHarga,
                'detail_pesanan' => $detailPesanan,
            ],
        ], 200);
    }
    
}
