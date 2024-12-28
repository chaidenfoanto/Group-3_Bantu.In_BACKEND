<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisController;
use App\Http\Controllers\TukangController;
use App\Http\Controllers\LocationController;

//user
Route::post('/regists', [RegisController::class, 'registersUser']); // bisa
Route::post('/login', [RegisController::class, 'loginUser'])->name('login');

//tukang
Route::post('/registukangs', [TukangController::class, 'registersTukang']); // bisa
Route::post('/tukanglogin', [TukangController::class, 'loginTukang']); // bisa

// Grup untuk middleware 'auth:sanctum'
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/customers', [RegisController::class, 'indexUser']); // bisa 
    Route::get('/customers', [RegisController::class, 'showUser']); // bisa 
    Route::put('/customers', [RegisController::class, 'updateUser']); // 
    Route::delete('/customers', [RegisController::class, 'destroyUser']); // bisa

    Route::post('/logoutuser', [\App\Http\Controllers\RegisController::class, 'logout']);

    Route::post('/lokasiuser', [LocationController::class, 'store']);

    Route::patch('/location/start', [LocationController::class, 'start']);

    // Route untuk mengakhiri perjalanan
    Route::patch('/location/end', [LocationController::class, 'end']);
    
    // buat get untuk user dapat tukangnya
    Route::get('/lokasitukangterdekat', [LocationController::class, 'getNearestTukang']);
    // Route::get('/lokasitukangterdekat/{id_user}', [LocationController::class, 'getNearestTukang']);

    Route::get('/randomtukang', [LocationController::class, 'getRandomTukang']);

});

// Grup untuk middleware 'auth:tukang'
Route::middleware('auth:tukang')->group(function () {
    Route::get('/tukangs', function (Request $request) {
        return $request->user();
    });

    Route::get('/tukangsa', [TukangController::class, 'indexTukang']); //bisa
    Route::get('/tukangspropil', [TukangController::class, 'showTukang']); // bisa
    Route::put('/tukangsupdate', [TukangController::class, 'updateTukang']); // bisa
    Route::delete('/tukangshapus', [TukangController::class, 'destroyTukang']); // bisa

    Route::post('/logouttukang', [\App\Http\Controllers\TukangController::class, 'logout']);

    Route::put('/lokasiuser/tukang/{id_tukang}/update', [LocationController::class, 'updateLocation']);
});
