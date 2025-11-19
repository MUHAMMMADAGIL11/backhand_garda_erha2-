<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth; // Pastikan ini ditambahkan
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    protected $userService;

    // Inject the UserService into the controller
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request): JsonResponse
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:50|unique:users,username',
                'password' => 'required|string|min:6',
                'nama_lengkap' => 'nullable|string|max:255',
                'role' => 'nullable|string|max:255',
            ]);

            // If validation fails, return error response
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 400); // HTTP Status Code 400 Bad Request
            }

            // Call the service method to register the user
            $user = $this->userService->register($request->all());

            // Return the response with a success message and the user data
            return response()->json([
                'message' => 'User registered successfully!',
                'user' => $user,
            ], 201); // HTTP Status Code 201 Created
        } catch (\Exception $e) {
            // Handle any errors (validation or others)
            return response()->json([
                'error' => $e->getMessage(),
            ], 400); // HTTP Status Code 400 Bad Request
        }
    }

    /**
     * Handle user login and return JWT token.
     */
    public function login(Request $request)
    {
        // Validate the input data
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json(['message' => 'Username and password are required'], Response::HTTP_BAD_REQUEST);
        }

        // Extract the credentials from validated data
        $credentials = $request->only('username', 'password');

        // Find the user by username
        $user = User::where('username', $credentials['username'])->first();

        // Check if user exists and if the password matches
        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Generate JWT token using JWTAuth
            $token = JWTAuth::fromUser($user);

            // Return success response with user data and token
            return response()->json([
                'message' => 'Login successful',
                'username' => $user->username,
                'nama_lengkap' => $user->nama_lengkap,
                'role' => $user->role,
                'token' => $token
            ], Response::HTTP_OK)->cookie('token', $token, 60 * 24); // Token expires in 1 day
        }

        // Return error response if login failed
        return response()->json(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
    }

   public function logout(Request $request)
{
    // Ambil token dari header atau cookie
    $token = $request->bearerToken() ?: $request->cookie('token');

    if ($token) {
        // Menghapus cookie 'token' dengan setting waktu kadaluarsa ke waktu yang telah lalu
        return response()->json(['message' => 'Logout successful'])
            ->withCookie(cookie('token', null, -1)); // Hapus cookie dengan waktu kadaluarsa -1
    }

    return response()->json(['message' => 'Token not found'], 400);
}

    /**
     * Method khusus untuk AdminGudang: kelolaUser() - sesuai class diagram
     * GET /admin-gudang/kelola-user - Melihat semua user
     * GET /admin-gudang/kelola-user/{id} - Melihat detail user
     * POST /admin-gudang/kelola-user - Menambah user baru
     * PUT /admin-gudang/kelola-user/{id} - Mengupdate user
     * DELETE /admin-gudang/kelola-user/{id} - Menghapus user
     */
    
    // GET - Melihat semua user
    public function indexKelolaUser(Request $request)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa mengelola user
            if (!$user->hasRole('AdminGudang')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat mengelola user'
                ], 403);
            }
                
            $users = User::all();
            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // GET - Melihat detail user
    public function showKelolaUser(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa mengelola user
            if (!$user->hasRole('AdminGudang')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat mengelola user'
                ], 403);
            }

            $targetUser = User::find($id);
            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }
            return response()->json([
                'success' => true,
                'data' => $targetUser
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST - Menambah user baru
    public function storeKelolaUser(Request $request)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa mengelola user
            if (!$user->hasRole('AdminGudang')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat mengelola user'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:50|unique:users,username',
                'password' => 'required|string|min:6',
                'nama_lengkap' => 'nullable|string|max:255',
                'role' => 'required|string|in:AdminGudang,PetugasOperasional,KepalaDivisi',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $newUser = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'nama_lengkap' => $request->nama_lengkap,
                'role' => $request->role,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan',
                'data' => $newUser
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // PUT - Mengupdate user
    public function updateKelolaUser(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa mengelola user
            if (!$user->hasRole('AdminGudang')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat mengelola user'
                ], 403);
            }

            $targetUser = User::find($id);
            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'username' => 'sometimes|string|max:50|unique:users,username,' . $id . ',id_user',
                'password' => 'sometimes|string|min:6',
                'nama_lengkap' => 'sometimes|string|max:255',
                'role' => 'sometimes|string|in:AdminGudang,PetugasOperasional,KepalaDivisi',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = $request->only(['username', 'nama_lengkap', 'role']);
            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $targetUser->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui',
                'data' => $targetUser
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // DELETE - Menghapus user
    public function destroyKelolaUser(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang yang bisa mengelola user
            if (!$user->hasRole('AdminGudang')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Admin Gudang yang dapat mengelola user'
                ], 403);
            }

            $targetUser = User::find($id);
            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            $targetUser->delete();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
