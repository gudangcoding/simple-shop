<?php

use App\Http\Controllers\API\BarangController;
use App\Http\Controllers\API\PesananController;
use App\Http\Controllers\API\ProfilController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::post('/user/register', [UserController::class, 'register']);
Route::post('/user/login', [UserController::class, 'login']);
Route::get('/user/aktifasi', [UserController::class, 'aktifasi']);
Route::get('/user/reset', [UserController::class, 'reset']);


//Barang   
Route::get('/barang', [BarangController::class, 'index']);
// Route::get('/barang', [BarangController::class, 'index'])->middleware('auth:sanctum');

//User group route
Route::group(['prefix' => '/user', 'middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [ProfilController::class, 'logout']);
    Route::get('/profil/{id}', [ProfilController::class, 'index']);
    Route::post('/update', [ProfilController::class, 'update']);
});

//Pesanan group route
Route::group(['prefix' => '/order', 'middleware' => ['auth:sanctum']], function () {
    Route::post('/detail', [PesananController::class, 'index']);
    Route::post('/store', [PesananController::class, 'store']);
    Route::get('/checkout', [PesananController::class, 'check_out']);
    Route::get('/konfirmasi', [PesananController::class, 'konfirmasi']);
    Route::get('/listpesanan', [PesananController::class, 'list_pesanan']);
    Route::get('/detail/{id}', [PesananController::class, 'detail_pesanan']);
    Route::get('/bayar', [PesananController::class, 'store']);
    Route::get('/andle', [PesananController::class, 'handle']);
    Route::get('/status', [PesananController::class, 'status']);
});
