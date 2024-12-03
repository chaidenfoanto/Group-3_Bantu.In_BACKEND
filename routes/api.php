<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisController;
use App\Http\Controllers\TukangController;

//user
Route::get('/customers', [RegisController::class, 'indexUser']); // bisa 
Route::get('/customers/{id_user}', [RegisController::class, 'showUser']); // bisa 
Route::post('/regists', [RegisController::class, 'registersUser']); // bisa
Route::put('/customers/{id_user}', [RegisController::class, 'updateUser']); // 
Route::delete('/customers/{id_user}', [RegisController::class, 'destroyUser']); // bisa
Route::post('/login', [RegisController::class, 'loginUser']); // bisa

//tukang
Route::get('/tukangs', [TukangController::class, 'indexTukang']); //bisa
Route::get('/tukangs/{tukang_id}', [TukangController::class, 'showTukang']); // bisa
Route::post('/registukangs', [TukangController::class, 'registersTukang']); // bisa
Route::put('/tukangs/{tukang_id}', [TukangController::class, 'updateTukang']); // bisa
Route::delete('/tukangs/{tukang_id}', [TukangController::class, 'destroyTukang']); // bisa
Route::post('/tukanglogin', [TukangController::class, 'loginTukang']); // bisa
