<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\TransaksiKeluar;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransaksiKeluarController extends Controller
{
    // GET /transaksi-keluar - Melihat semua barang keluar
    public function index(Request $request)
    {
        try {
            $transaksi = Transaksi::where('jenis_transaksi', 'KELUAR')
                ->with(['user', 'barang.kategori', 'transaksiKeluar'])
                ->orderBy('tanggal', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $transaksi
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data transaksi keluar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST /transaksi-keluar - Mencatat barang keluar & mengurangi stok
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa mencatat transaksi keluar
            if ($user->role !== 'AdminGudang') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat mencatat transaksi keluar'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'id_barang' => 'required|integer|exists:barang,id_barang',
                'tanggal' => 'required|date',
                'jumlah' => 'required|integer|min:1',
                'tujuan' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cek stok tersedia
            $barang = Barang::find($request->id_barang);
            if ($barang->stok < $request->jumlah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $barang->stok
                ], 400);
            }

            // Buat transaksi
            $transaksi = Transaksi::create([
                'id_user' => $user->id_user,
                'id_barang' => $request->id_barang,
                'jenis_transaksi' => 'KELUAR',
                'tanggal' => $request->tanggal,
                'jumlah' => $request->jumlah,
            ]);

            // Buat detail transaksi keluar
            $transaksiKeluar = TransaksiKeluar::create([
                'id_transaksi' => $transaksi->id_transaksi,
                'tujuan' => $request->tujuan,
            ]);

            // Kurangi stok barang
            $barang->decrement('stok', $request->jumlah);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi keluar berhasil dicatat',
                'data' => $transaksi->load(['user', 'barang.kategori', 'transaksiKeluar'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat transaksi keluar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

