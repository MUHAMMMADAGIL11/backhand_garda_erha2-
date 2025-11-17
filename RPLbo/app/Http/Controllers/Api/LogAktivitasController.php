<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class LogAktivitasController extends Controller
{
    // GET /logs - Melihat semua aktivitas (admin)
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa melihat semua log
            if ($user->role !== 'AdminGudang') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat melihat semua log aktivitas'
                ], 403);
            }

            $logs = LogAktivitas::with('user')
                ->orderBy('timestamp', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil log aktivitas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // GET /logs/user/{id} - Melihat aktivitas tertentu milik user
    public function getUserLogs(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Admin bisa melihat semua, user hanya bisa melihat log sendiri
            if ($user->role !== 'AdminGudang' && $user->id_user != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk melihat log ini'
                ], 403);
            }

            $logs = LogAktivitas::where('id_user', $id)
                ->orderBy('timestamp', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil log aktivitas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

