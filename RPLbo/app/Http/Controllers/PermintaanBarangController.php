<?php

namespace App\Http\Controllers;

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
            if ($user->hasRole('PetugasOperasional')) {
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
            if (!$user->hasRole('PetugasOperasional')) {
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
            if (!$user->hasRole('AdminGudang')) {
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
            if (!$user->hasRole('AdminGudang')) {
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

    // PUT /permintaan-barang/{id} - Memperbarui status permintaan barang
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $permintaan = PermintaanBarang::find($id);

            if (!$permintaan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permintaan tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:Menunggu Persetujuan,Disetujui,Ditolak,Selesai',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Hanya AdminGudang yang bisa mengubah status
            if (!$user->hasRole('AdminGudang')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat memperbarui status permintaan'
                ], 403);
            }

            $permintaan->update(['status' => $request->status]);

            // Jika disetujui, kurangi stok
            if ($request->status === 'Disetujui' && $permintaan->status !== 'Disetujui') {
                if ($permintaan->barang->stok < $permintaan->jumlah_diminta) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $permintaan->barang->stok
                    ], 400);
                }
                $permintaan->barang->decrement('stok', $permintaan->jumlah_diminta);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status permintaan berhasil diperbarui',
                'data' => $permintaan->load(['user', 'barang.kategori'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui permintaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST /petugas-operasional/menyelesaikan-permintaan - Menyelesaikan permintaan barang
    public function selesaikanPermintaan(Request $request)
    {
        try {
            $user = $request->user();
            
            // Hanya PetugasOperasional yang bisa menyelesaikan permintaan
            if (!$user->hasRole('PetugasOperasional')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Petugas Operasional yang dapat menyelesaikan permintaan'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'id_permintaan' => 'required|integer|exists:permintaan_barang,id_permintaan',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $permintaan = PermintaanBarang::find($request->id_permintaan);

            if (!$permintaan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permintaan tidak ditemukan'
                ], 404);
            }

            // Hanya bisa menyelesaikan permintaan yang sudah disetujui
            if ($permintaan->status !== 'Disetujui') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya permintaan yang sudah disetujui yang dapat diselesaikan'
                ], 400);
            }

            // Update status menjadi selesai
            $permintaan->update(['status' => 'Selesai']);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan berhasil diselesaikan',
                'data' => $permintaan->load(['user', 'barang.kategori'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan permintaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

