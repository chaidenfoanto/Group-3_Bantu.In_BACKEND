<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisController;

Route::get('/customers', [RegisController::class, 'index']);
Route::get('/customers/{id}', [RegisController::class, 'show']);
Route::post('/regists', [RegisController::class, 'registers']);
Route::put('/customers/{id}', [RegisController::class, 'update']);
Route::delete('/customers/{id}', [RegisController::class, 'destroy']);
Route::post('/login', [RegisController::class, 'login']);
