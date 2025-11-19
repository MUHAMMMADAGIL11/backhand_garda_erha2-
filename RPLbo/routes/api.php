<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\LogAktivitasController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PermintaanBarangController;
use App\Http\Controllers\TransaksiMasukController;
use App\Http\Controllers\TransaksiKeluarController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;

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
    Route::post('register', [UserController::class, 'register']);
    
    // Endpoint Login
    Route::post('login', [UserController::class, 'login']);
});

// ROUTE NOTIFIKASI
Route::middleware(['jwt.cookie','auth:api'])->group(function () {
    Route::post('logout', [UserController::class, 'logout']);

    Route::get('notifikasi', [NotifikasiController::class, 'index']);
    Route::post('notifikasi', [NotifikasiController::class, 'store']);
    Route::patch('notifikasi/{id}/read', [NotifikasiController::class, 'markAsRead']);
    Route::delete('notifikasi/{id}', [NotifikasiController::class, 'destroy']);
});

// ROUTE LOG AKTIVITAS
Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
    Route::get('logaktivitas', [LogAktivitasController::class, 'index']);
    Route::post('logaktivitas', [LogAktivitasController::class, 'store']);
    Route::get('logaktivitas/user/{id}', [LogAktivitasController::class, 'getUserLogs']);
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
    Route::get('permintaan-barang', [PermintaanBarangController::class, 'index']);
    Route::get('permintaan-barang/{id}', [PermintaanBarangController::class, 'show']);
    Route::post('permintaan-barang', [PermintaanBarangController::class, 'store']);
    Route::put('permintaan-barang/{id}', [PermintaanBarangController::class, 'update']);
    Route::patch('permintaan-barang/{id}/approve', [PermintaanBarangController::class, 'approve']);
    Route::patch('permintaan-barang/{id}/reject', [PermintaanBarangController::class, 'reject']);
});

// ROUTE TRANSAKSI MASUK
Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
    Route::get('transaksi/masuk', [TransaksiMasukController::class, 'index']);
    Route::post('transaksi/masuk', [TransaksiMasukController::class, 'store']);
    Route::put('transaksi/masuk/{id}', [TransaksiMasukController::class, 'update']);
});

// ROUTE TRANSAKSI KELUAR
Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
    Route::get('transaksi/keluar', [TransaksiKeluarController::class, 'index']);
    Route::post('transaksi/keluar', [TransaksiKeluarController::class, 'store']);
    Route::put('transaksi/keluar/{id}', [TransaksiKeluarController::class, 'update']);
});

// ROUTE LAPORAN
Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
    Route::get('laporan', [LaporanController::class, 'index']);
    Route::get('laporan/{id}', [LaporanController::class, 'show']);
    Route::post('laporan', [LaporanController::class, 'store']);
    Route::post('laporan/export', [LaporanController::class, 'export']);
    Route::get('laporan/{id}/pdf', [LaporanController::class, 'downloadPdf']);
    Route::get('laporan/{id}/excel', [LaporanController::class, 'downloadExcel']);
});

// ROUTE ADMIN GUDANG
Route::middleware(['jwt.cookie', 'auth:api'])->prefix('admin-gudang')->group(function () {
    // +tambahBarang(): boolean - sesuai class diagram
    Route::post('tambah-barang', [BarangController::class, 'store']);
    
    // +catatBarangMasuk(): boolean - sesuai class diagram
    Route::post('catat-barang-masuk', [TransaksiMasukController::class, 'store']);
    
    // +catatBarangKeluar(): boolean - sesuai class diagram
    Route::post('catat-barang-keluar', [TransaksiKeluarController::class, 'store']);
    
    // +kelolaUser(): boolean - sesuai class diagram
    Route::get('kelola-user', [UserController::class, 'indexKelolaUser']);
    Route::get('kelola-user/{id}', [UserController::class, 'showKelolaUser']);
    Route::post('kelola-user', [UserController::class, 'storeKelolaUser']);
    Route::put('kelola-user/{id}', [UserController::class, 'updateKelolaUser']);
    Route::delete('kelola-user/{id}', [UserController::class, 'destroyKelolaUser']);
    
    // +buatLaporan(): Laporan - sesuai class diagram
    Route::post('buat-laporan', [LaporanController::class, 'store']);
});

// ROUTE PETUGAS OPERASIONAL
Route::middleware(['jwt.cookie', 'auth:api'])->prefix('petugas-operasional')->group(function () {
    // +lihatStokBarang(): array - sesuai class diagram
    Route::get('lihat-stok-barang', [BarangController::class, 'lihatStokBarang']);
    
    // +ajukanPermintaan(): boolean - sesuai class diagram
    Route::post('ajukan-permintaan', [PermintaanBarangController::class, 'store']);
    
    // Method tambahan yang sudah ada
    Route::post('menambahkan-transaksi', [TransaksiKeluarController::class, 'store']);
    Route::post('menyelesaikan-permintaan', [PermintaanBarangController::class, 'selesaikanPermintaan']);
});

// ROUTE KEPALA DIVISI
Route::middleware(['jwt.cookie', 'auth:api'])->prefix('kepala-divisi')->group(function () {
    // +buatLaporan(): Laporan - sesuai class diagram
    Route::post('buat-laporan', [LaporanController::class, 'buatLaporan']);
    
    // +cetakLaporan(): File - sesuai class diagram
    Route::post('cetak-laporan', [LaporanController::class, 'cetakLaporan']);
    Route::get('cetak-laporan/{id}', [LaporanController::class, 'cetakLaporan']);
});

// Route yang dilindungi (Opsional, untuk menguji token)
Route::middleware(['jwt.cookie', 'auth:api'])->get('/user', function (Request $request) {
    $user = $request->user();
    return response()->json([
        'success' => true,
        'user' => [
            'id_user' => $user->id_user,
            'username' => $user->username,
            'nama_lengkap' => $user->nama_lengkap,
            'role' => $user->role,
            'is_active' => $user->is_active,
        ]
    ]);
});
