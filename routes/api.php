<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisController;

Route::get('/customers', [RegisController::class, 'index']); // bisa 
Route::get('/customers/{user_id}', [RegisController::class, 'show']); // bisa 
Route::post('/regists', [RegisController::class, 'registers']); // bisa
Route::put('/customers/{user_id}', [RegisController::class, 'update']); // 
Route::delete('/customers/{user_id}', [RegisController::class, 'destroy']); // bisa
Route::post('/login', [RegisController::class, 'login']); // bisa
