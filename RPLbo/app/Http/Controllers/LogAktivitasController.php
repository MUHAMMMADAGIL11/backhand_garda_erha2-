<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LogAktivitasController extends Controller
{
    // GET /logs - Melihat semua aktivitas (admin)
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa melihat semua log
            if (!$user->hasRole('AdminGudang')) {
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
            if (!$user->hasRole('AdminGudang') && $user->id_user != $id) {
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

    // POST /logaktivitas - Mencatat aktivitas baru
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            
            $validator = Validator::make($request->all(), [
                'aktivitas' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $log = LogAktivitas::create([
                'id_user' => $user->id_user,
                'aktivitas' => $request->aktivitas,
                'timestamp' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aktivitas berhasil dicatat',
                'data' => $log->load('user')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat aktivitas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

