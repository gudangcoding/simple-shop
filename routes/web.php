<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\HistoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('pesan/{id}', [PesananController::class,'index']);
Route::post('pesan/{id}', [PesananController::class,'pesan'])->name('pesan');
Route::get('check-out', [PesananController::class,'check_out']);
Route::delete('check-out/{id}', [PesananController::class,'delete']);

Route::get('konfirmasi-check-out', [PesananController::class,'konfirmasi']);

Route::get('profile', [ProfilController::class,'index']);
Route::post('profile', [ProfilController::class,'update']);

Route::get('history', [HistoryController::class,'index']);
Route::get('history/{id}', [HistoryController::class,'detail']);