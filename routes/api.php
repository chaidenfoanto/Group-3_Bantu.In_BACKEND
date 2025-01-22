<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisController;
use App\Http\Controllers\TukangController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\CommentsUserController;
use App\Http\Controllers\CommentsTukangController;
use App\Http\Controllers\DetailPesananController;
use App\Http\Controllers\TukangUpdateHistory;
use App\Http\Controllers\PesananChooseController;
use App\Http\Controllers\EducationController;

//user
Route::post('/regists', [RegisController::class, 'registersUser']); // bisa
Route::post('/login', [RegisController::class, 'loginUser'])->name('login');

//tukang
Route::post('/registukangs', [TukangController::class, 'registersTukang']); // bisa
Route::post('/tukanglogin', [TukangController::class, 'loginTukang']); // bisa

Route::put('/lupapass', [RegisController::class, 'lupaPassword']); //

// Grup untuk middleware 'auth:sanctum'
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/user-dashboard', function (Request $request) {
        return response()->json([
            'name' => $request->user()->name,
        ]);
    });

    Route::get('/user-profile', function (Request $request) {
        return response()->json([
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'no_hp' => $request->user()->no_hp,
            'foto_diri' => $request->user()->foto_diri,
            'password_confirmation' => $request->user()->password_confirmation
        ]);
    });

    Route::get('/customers', [RegisController::class, 'showUser']); // bisa
    Route::put('/customers', [RegisController::class, 'updateUser']); //
    Route::put('/resetpass', [RegisController::class, 'resetPassword']); //
    Route::delete('/customers', [RegisController::class, 'destroyUser']); // bisa
    Route::patch('/updatefotouser', [RegisController::class, 'updateFotoDiri']); //

    Route::post('/logoutuser', [\App\Http\Controllers\RegisController::class, 'logout']);

    Route::post('/lokasiuser', [LocationController::class, 'store']);

    Route::patch('/location/start', [LocationController::class, 'start']);

    // Route untuk mengakhiri perjalanan
    Route::patch('/location/end', [LocationController::class, 'end']);

    // buat get untuk user dapat tukangnya
    Route::get('/lokasitukangterdekat', [LocationController::class, 'getNearestTukang']);
    Route::get('/lokasitukangterdekattarik', [LocationController::class, 'getNearestTukangTarik']);
    // Route::get('/lokasitukangterdekat/{id_user}', [LocationController::class, 'getNearestTukang']);

    Route::post('/pesan/tukang', [LocationController::class, 'postTukangToPesanan']);
    Route::post('/bookingharitertentu/tukang', [PesananChooseController::class, 'ChooseDate']);
    // Route::post('/pesanantukangambil', [LocationController::class, 'postTukangToPesananambil']);


    Route::get('/pesanan/detail', [DetailPesananController::class, 'getDetailPesanan']);
    Route::put('/pesanandetail', [DetailPesananController::class, 'putDetailPesanandeskripsiservis']);
    Route::get('/detailbayar/{id_pesanan}', [DetailPesananController::class, 'getDetailAkhir']);

    Route::put('/ulasan/tukang/{id_tukang}', [CommentsTukangController::class, 'kasihulasanuser']);
    Route::post('/rating/tukang/{id_tukang}', [CommentsTukangController::class, 'kasihratinguser']);
    Route::get('/lihatratingxulasan/{id_tukang}', [CommentsController::class, 'getKomentarTukang']);

    //edukasi
    Route::get('/education/videos', [EducationController::class, 'getVideos']);
    Route::get('/education/facts', [EducationController::class, 'getFacts']);
    Route::get('/education/facts/{id}', [EducationController::class, 'getFactDetail']);
});

// Grup untuk middleware 'auth:tukang'
Route::middleware('auth:tukang')->group(function () {
    Route::get('/tukangs', function (Request $request) {
        return $request->user();
    });

    Route::get('/tukangspropil', [TukangController::class, 'showTukang']); // bisa
    Route::put('/tukangsupdate', [TukangController::class, 'updateTukang']); // bisa
    Route::delete('/tukangshapus', [TukangController::class, 'destroyTukang']); // bisa
    Route::put('/resetpasstukang', [TukangController::class, 'resetPassword']);
    Route::patch('/updatefototukang', [TukangController::class, 'updateFotoDiri']);

    Route::post('/logouttukang', [\App\Http\Controllers\TukangController::class, 'logout']);

    Route::put('/lokasiuser/tukang/update', [LocationController::class, 'updateLocation']);

    Route::put('/ulasan/user/{id_user}', [CommentsUserController::class, 'kasihulasanuser']);
    Route::post('/rating/user/{id_user}', [CommentsUserController::class, 'kasihratinguser']);
    Route::get('/lihatratingxulasan/{id_user}', [CommentsUserController::class, 'getKomentarUser']);

    Route::put('/putstatus', [TukangUpdateHistory::class, 'tukangput']);
    Route::get('/getpesanandariuser', [TukangUpdateHistory::class, 'tukangtampilkanpesanan']);

    Route::put('/waktuserviceupdate', [LocationController::class, 'updateWaktuServis']);
});
