<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PermintaanBarang;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermintaanBarangController extends Controller
{
    // GET /permintaan - Melihat semua permintaan barang
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $query = PermintaanBarang::with(['user', 'barang.kategori']);

            // Petugas hanya melihat permintaan sendiri, Admin melihat semua
            if ($user->role === 'PetugasOperasional') {
                $query->where('id_user', $user->id_user);
            }

            $permintaan = $query->orderBy('id_permintaan', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $permintaan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data permintaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // GET /permintaan/{id} - Detail permintaan
    public function show($id)
    {
        try {
            $permintaan = PermintaanBarang::with(['user', 'barang.kategori'])->find($id);

            if (!$permintaan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permintaan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $permintaan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail permintaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST /permintaan - Petugas mengajukan permintaan barang
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            
            // Hanya PetugasOperasional yang bisa mengajukan permintaan
            if ($user->role !== 'PetugasOperasional') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Petugas Operasional yang dapat mengajukan permintaan'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'id_barang' => 'required|integer|exists:barang,id_barang',
                'jumlah_diminta' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $permintaan = PermintaanBarang::create([
                'id_user' => $user->id_user,
                'id_barang' => $request->id_barang,
                'jumlah_diminta' => $request->jumlah_diminta,
                'status' => 'Menunggu Persetujuan',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan berhasil diajukan',
                'data' => $permintaan->load(['user', 'barang.kategori'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengajukan permintaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // PATCH /permintaan/{id}/approve - Admin menyetujui permintaan
    public function approve(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa menyetujui
            if ($user->role !== 'AdminGudang') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat menyetujui permintaan'
                ], 403);
            }

            $permintaan = PermintaanBarang::with('barang')->find($id);

            if (!$permintaan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permintaan tidak ditemukan'
                ], 404);
            }

            if ($permintaan->status !== 'Menunggu Persetujuan') {
                return response()->json([
                    'success' => false,
                    'message' => 'Permintaan sudah diproses'
                ], 400);
            }

            // Cek stok tersedia
            if ($permintaan->barang->stok < $permintaan->jumlah_diminta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $permintaan->barang->stok
                ], 400);
            }

            // Update status
            $permintaan->update(['status' => 'Disetujui']);

            // Kurangi stok barang
            $permintaan->barang->decrement('stok', $permintaan->jumlah_diminta);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan berhasil disetujui',
                'data' => $permintaan->load(['user', 'barang.kategori'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui permintaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // PATCH /permintaan/{id}/reject - Admin menolak permintaan
    public function reject(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa menolak
            if ($user->role !== 'AdminGudang') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat menolak permintaan'
                ], 403);
            }

            $permintaan = PermintaanBarang::find($id);

            if (!$permintaan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permintaan tidak ditemukan'
                ], 404);
            }

            if ($permintaan->status !== 'Menunggu Persetujuan') {
                return response()->json([
                    'success' => false,
                    'message' => 'Permintaan sudah diproses'
                ], 400);
            }

            $permintaan->update(['status' => 'Ditolak']);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan ditolak',
                'data' => $permintaan->load(['user', 'barang.kategori'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak permintaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

