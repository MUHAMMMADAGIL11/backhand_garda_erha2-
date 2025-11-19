<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\TransaksiMasuk;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransaksiMasukController extends Controller
{
    // GET /transaksi-masuk - Melihat semua barang masuk
    public function index(Request $request)
    {
        try {
            $transaksi = Transaksi::where('jenis_transaksi', 'MASUK')
                ->with(['user', 'barang.kategori', 'transaksiMasuk'])
                ->orderBy('tanggal', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $transaksi
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data transaksi masuk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST /transaksi-masuk - Mencatat barang masuk & menambah stok
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa mencatat transaksi masuk
            if (!$user->hasRole('AdminGudang')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat mencatat transaksi masuk'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'id_barang' => 'required|integer|exists:barang,id_barang',
                'tanggal' => 'required|date',
                'jumlah' => 'required|integer|min:1',
                'supplier' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buat transaksi
            $transaksi = Transaksi::create([
                'id_user' => $user->id_user,
                'id_barang' => $request->id_barang,
                'jenis_transaksi' => 'MASUK',
                'tanggal' => $request->tanggal,
                'jumlah' => $request->jumlah,
            ]);

            // Buat detail transaksi masuk
            $transaksiMasuk = TransaksiMasuk::create([
                'id_transaksi' => $transaksi->id_transaksi,
                'supplier' => $request->supplier,
            ]);

            // Tambah stok barang
            $barang = Barang::find($request->id_barang);
            $barang->increment('stok', $request->jumlah);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi masuk berhasil dicatat',
                'data' => $transaksi->load(['user', 'barang.kategori', 'transaksiMasuk'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat transaksi masuk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // PUT /transaksi/masuk/{id} - Memperbarui status transaksi masuk
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa update transaksi
            if (!$user->hasRole('AdminGudang')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat memperbarui transaksi masuk'
                ], 403);
            }

            $transaksi = Transaksi::where('id_transaksi', $id)
                ->where('jenis_transaksi', 'MASUK')
                ->first();

            if (!$transaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi masuk tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'nullable|string',
                'supplier' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update transaksi masuk detail jika ada
            if ($request->has('supplier') && $transaksi->transaksiMasuk) {
                $transaksi->transaksiMasuk->update([
                    'supplier' => $request->supplier
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi masuk berhasil diperbarui',
                'data' => $transaksi->load(['user', 'barang.kategori', 'transaksiMasuk'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui transaksi masuk',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

