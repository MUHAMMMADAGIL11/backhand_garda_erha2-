<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotifikasiController extends Controller
{
    // GET /notifikasi - Melihat semua notifikasi user
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $notifikasi = Notifikasi::where('id_user', $user->id_user)
                ->orderBy('id_notifikasi', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $notifikasi
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST /notifikasi - Admin mengirim notifikasi ke user
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa mengirim notifikasi
            if ($user->role !== 'AdminGudang') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat mengirim notifikasi'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'id_user' => 'required|integer|exists:users,id_user',
                'pesan' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $notifikasi = Notifikasi::create([
                'id_user' => $request->id_user,
                'pesan' => $request->pesan,
                'is_read' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dikirim',
                'data' => $notifikasi
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // PATCH /notifikasi/{id}/read - Menandai notifikasi sudah dibaca
    public function markAsRead(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $notifikasi = Notifikasi::where('id_notifikasi', $id)
                ->where('id_user', $user->id_user)
                ->first();

            if (!$notifikasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi tidak ditemukan'
                ], 404);
            }

            $notifikasi->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sebagai sudah dibaca',
                'data' => $notifikasi
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // DELETE /notifikasi/{id} - Menghapus notifikasi
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $notifikasi = Notifikasi::where('id_notifikasi', $id)
                ->where('id_user', $user->id_user)
                ->first();

            if (!$notifikasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi tidak ditemukan'
                ], 404);
            }

            $notifikasi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

