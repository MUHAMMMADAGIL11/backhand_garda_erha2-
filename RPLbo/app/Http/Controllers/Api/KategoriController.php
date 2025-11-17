<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KategoriController extends Controller
{
    // GET /kategori - Melihat daftar kategori barang
    public function index()
    {
        try {
            $kategori = Kategori::orderBy('nama_kategori', 'asc')->get();

            return response()->json([
                'success' => true,
                'data' => $kategori
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST /kategori - Menambah kategori
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa menambah kategori
            if ($user->role !== 'AdminGudang') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat menambah kategori'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'nama_kategori' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $kategori = Kategori::create([
                'nama_kategori' => $request->nama_kategori,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil ditambahkan',
                'data' => $kategori
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // PUT /kategori/{id} - Mengubah kategori
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa mengubah kategori
            if ($user->role !== 'AdminGudang') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat mengubah kategori'
                ], 403);
            }

            $kategori = Kategori::find($id);

            if (!$kategori) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nama_kategori' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $kategori->update(['nama_kategori' => $request->nama_kategori]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diperbarui',
                'data' => $kategori
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // DELETE /kategori/{id} - Menghapus kategori
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa menghapus kategori
            if ($user->role !== 'AdminGudang') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat menghapus kategori'
                ], 403);
            }

            $kategori = Kategori::find($id);

            if (!$kategori) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }

            $kategori->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

