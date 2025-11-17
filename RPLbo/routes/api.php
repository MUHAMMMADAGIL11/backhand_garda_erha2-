<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotifikasiController;
use App\Http\Controllers\Api\LogAktivitasController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\PermintaanBarangController;
use App\Http\Controllers\Api\TransaksiMasukController;
use App\Http\Controllers\Api\TransaksiKeluarController;
use App\Http\Controllers\Api\LaporanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Route di sini otomatis mendapatkan prefix /api/
*/

// ROUTE AUTHENTIKASI JWT
Route::group([
    'prefix' => 'auth' 
], function ($router) {
    // Endpoint Register
    Route::post('register', [AuthController::class, 'register']);
    
    // Endpoint Login
    Route::post('login', [AuthController::class, 'login']);

    // Endpoint get current user
    Route::get('me', [AuthController::class, 'me'])->middleware(['jwt.cookie', 'auth:api']);

    // Endpoint Profile
    Route::get('profile', [AuthController::class, 'profile'])->middleware(['jwt.cookie', 'auth:api']);

    // Endpoint Logout
    Route::post('logout', [AuthController::class, 'logout'])->middleware(['jwt.cookie', 'auth:api']);
});

// ROUTE NOTIFIKASI
Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
    Route::get('notifikasi', [NotifikasiController::class, 'index']);
    Route::post('notifikasi', [NotifikasiController::class, 'store']);
    Route::patch('notifikasi/{id}/read', [NotifikasiController::class, 'markAsRead']);
    Route::delete('notifikasi/{id}', [NotifikasiController::class, 'destroy']);
});

// ROUTE LOG AKTIVITAS
Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
    Route::get('logs', [LogAktivitasController::class, 'index']);
    Route::get('logs/user/{id}', [LogAktivitasController::class, 'getUserLogs']);
});

// ROUTE BARANG
Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
    Route::get('barang', [BarangController::class, 'index']);
    Route::get('barang/{id}', [BarangController::class, 'show']);
    Route::post('barang', [BarangController::class, 'store']);
    Route::put('barang/{id}', [BarangController::class, 'update']);
    Route::delete('barang/{id}', [BarangController::class, 'destroy']);
    Route::patch('barang/{id}/stok', [BarangController::class, 'updateStok']);
    Route::patch('barang/{id}/cek-minimum', [BarangController::class, 'cekMinimum']);
});

// ROUTE KATEGORI
Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
    Route::get('kategori', [KategoriController::class, 'index']);
    Route::post('kategori', [KategoriController::class, 'store']);
    Route::put('kategori/{id}', [KategoriController::class, 'update']);
    Route::delete('kategori/{id}', [KategoriController::class, 'destroy']);
});

// ROUTE PERMINTAAN BARANG
Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
    Route::get('permintaan', [PermintaanBarangController::class, 'index']);
    Route::get('permintaan/{id}', [PermintaanBarangController::class, 'show']);
    Route::post('permintaan', [PermintaanBarangController::class, 'store']);
    Route::patch('permintaan/{id}/approve', [PermintaanBarangController::class, 'approve']);
    Route::patch('permintaan/{id}/reject', [PermintaanBarangController::class, 'reject']);
});

// ROUTE TRANSAKSI MASUK
Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
    Route::get('transaksi-masuk', [TransaksiMasukController::class, 'index']);
    Route::post('transaksi-masuk', [TransaksiMasukController::class, 'store']);
});

// ROUTE TRANSAKSI KELUAR
Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
    Route::get('transaksi-keluar', [TransaksiKeluarController::class, 'index']);
    Route::post('transaksi-keluar', [TransaksiKeluarController::class, 'store']);
});

// ROUTE LAPORAN
Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
    Route::get('laporan', [LaporanController::class, 'index']);
    Route::get('laporan/{id}', [LaporanController::class, 'show']);
    Route::post('laporan', [LaporanController::class, 'store']);
    Route::get('laporan/{id}/pdf', [LaporanController::class, 'downloadPdf']);
    Route::get('laporan/{id}/excel', [LaporanController::class, 'downloadExcel']);
});

// Route yang dilindungi (Opsional, untuk menguji token)
Route::middleware(['jwt.cookie', 'auth:api'])->get('/user', function (Request $request) {
    return $request->user();
});