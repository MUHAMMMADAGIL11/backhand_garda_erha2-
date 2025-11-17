<?php

namespace App\Http\Controllers\Api; // <-- Pastikan namespace ini benar!

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // --- Helper untuk Mengirim Token ---
    protected function respondWithToken($token)
    {
        $ttl = Config::get('jwt.ttl', 60); // Default 60 menit
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttl * 60 
        ]);
    }

    // --- 🔑 REGISTER ---
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:50|unique:users,username',
                'password' => 'required|string|min:6|confirmed',
                'nama_lengkap' => 'required|string|max:255',
                'role' => 'required|in:AdminGudang,PetugasOperasional,KepalaDivisi',
                'divisi' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'nama_lengkap' => $request->nama_lengkap,
                'role' => $request->role,
                'divisi' => $request->divisi,
                'is_aktif' => true,
            ]);
            
            // Generate token setelah user dibuat
            $token = Auth::guard('api')->login($user);

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'data' => [
                    'user' => [
                        'id_user' => $user->id_user,
                        'username' => $user->username,
                        'nama_lengkap' => $user->nama_lengkap,
                        'role' => $user->role,
                        'divisi' => $user->divisi,
                    ],
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => Config::get('jwt.ttl', 60) * 60
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat registrasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // --- 🔓 LOGIN ---
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cari user berdasarkan username
            $user = User::where('username', $request->username)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username atau password salah'
                ], 401);
            }

            // Pastikan role termasuk salah satu role yang diizinkan
            $allowedRoles = ['AdminGudang', 'PetugasOperasional', 'KepalaDivisi'];
            if (!in_array($user->role, $allowedRoles, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role pengguna tidak diizinkan'
                ], 403);
            }

            // Cek apakah user aktif
            if (!$user->is_aktif) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda tidak aktif'
                ], 403);
            }

            // Generate token
            $token = Auth::guard('api')->login($user);
            $ttl = Config::get('jwt.ttl', 60);
            $cookie = cookie(
                name: 'access_token',
                value: $token,
                minutes: $ttl,
                path: '/',
                domain: null,
                secure: config('app.env') === 'production',
                httpOnly: true,
                raw: false,
                sameSite: 'lax'
            );

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => [
                        'id_user' => $user->id_user,
                        'username' => $user->username,
                        'nama_lengkap' => $user->nama_lengkap,
                        'role' => $user->role,
                        'divisi' => $user->divisi,
                    ],
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => $ttl * 60
                ]
            ])->cookie($cookie);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // --- 🙋‍♂️ GET CURRENT USER / PROFILE ---
    public function me(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada pengguna yang sedang login'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id_user' => $user->id_user,
                    'username' => $user->username,
                    'nama_lengkap' => $user->nama_lengkap,
                    'role' => $user->role,
                    'divisi' => $user->divisi,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // --- 👤 GET PROFILE (Alias untuk me) ---
    public function profile(Request $request)
    {
        return $this->me($request);
    }

    // --- 🔒 LOGOUT ---
    public function logout(Request $request)
    {
        $cookie = cookie()->forget('access_token');

        try {
            if ($token = $request->cookie('access_token')) {
                JWTAuth::setToken($token)->invalidate(true);
            }

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil'
            ])->cookie($cookie);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan logout',
                'error' => $e->getMessage()
            ], 500)->cookie($cookie);
        }
    }
}