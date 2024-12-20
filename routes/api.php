<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisController;
use App\Http\Controllers\TukangController;
use App\Http\Controllers\LocationController;

//user
Route::post('/regists', [RegisController::class, 'registersUser']); // bisa
Route::post('/login', [RegisController::class, 'loginUser']); // bisa

//tukang
Route::post('/registukangs', [TukangController::class, 'registersTukang']); // bisa
Route::post('/tukanglogin', [TukangController::class, 'loginTukang']); // bisa

// Grup untuk middleware 'auth:sanctum'
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/customers', [RegisController::class, 'indexUser']); // bisa 
    Route::get('/customers/{id_user}', [RegisController::class, 'showUser']); // bisa 
    Route::put('/customers/{id_user}', [RegisController::class, 'updateUser']); // 
    Route::delete('/customers/{id_user}', [RegisController::class, 'destroyUser']); // bisa

    Route::post('/logoutuser', [\App\Http\Controllers\RegisController::class, 'logout']);

    Route::get('/locatetukang/{id_user}', [LocationController::class, 'getTukangLocation']);
    Route::post('/start/{locate}', [LocationController::class, 'start']); // Start the trip
    Route::post('/end/{locate}', [LocationController::class, 'end']); // End the trip

    Route::put('/update-location/{locate}', [LocationController::class, 'updateLocation']);
});

// Grup untuk middleware 'auth:tukang'
Route::middleware('auth:tukang')->group(function () {
    Route::get('/tukangs', function (Request $request) {
        return $request->user();
    });

    Route::get('/tukangs', [TukangController::class, 'indexTukang']); //bisa
    Route::get('/tukangs/{tukang_id}', [TukangController::class, 'showTukang']); // bisa
    Route::put('/tukangs/{tukang_id}', [TukangController::class, 'updateTukang']); // bisa
    Route::delete('/tukangs/{tukang_id}', [TukangController::class, 'destroyTukang']); // bisa

    Route::post('/logouttukang', [\App\Http\Controllers\TukangController::class, 'logout']);
});
