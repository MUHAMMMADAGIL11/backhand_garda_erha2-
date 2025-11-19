<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_user';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_user',
        'username',
        'password',
        'nama_lengkap',
        'is_active',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Normalize role value untuk konsistensi
     * 
     * @param string|null $role
     * @return string|null
     */
    public static function normalizeRole($role)
    {
        if (!$role) {
            return null;
        }

        $role = trim($role);
        $roleLower = strtolower($role);
        
        // Mapping berbagai format role ke format standar
        $roleMap = [
            'admin gudang' => 'AdminGudang',
            'admingudang' => 'AdminGudang',
            'admin_gudang' => 'AdminGudang',
            'petugas operasional' => 'PetugasOperasional',
            'petugasoperasional' => 'PetugasOperasional',
            'petugas_operasional' => 'PetugasOperasional',
            'kepala divisi' => 'KepalaDivisi',
            'kepaladivisi' => 'KepalaDivisi',
            'kepala_divisi' => 'KepalaDivisi',
        ];

        return $roleMap[$roleLower] ?? $role;
    }

    /**
     * Cek apakah user memiliki role tertentu (case-insensitive)
     * 
     * @param string|array $expectedRole Role yang diharapkan atau array of roles
     * @return bool
     */
    public function hasRole($expectedRole)
    {
        $userRole = self::normalizeRole($this->role);
        
        if (is_array($expectedRole)) {
            // Jika array, cek apakah user memiliki salah satu role
            foreach ($expectedRole as $role) {
                $expectedRoleNormalized = self::normalizeRole($role);
                if ($userRole === $expectedRoleNormalized) {
                    return true;
                }
            }
            return false;
        }
        
        $expectedRoleNormalized = self::normalizeRole($expectedRole);
        return $userRole === $expectedRoleNormalized;
    }

    public function login($username, $password)
    {
        // Find the user by username
        $user = self::where('username', $username)->first();

        // Check if the user exists and if the password matches
        if ($user && Hash::check($password, $user->password)) {
            // Generate the JWT token if credentials are correct
            return JWTAuth::fromUser($user);
        }

        // Return false if login failed
        return false;
    }

    public function logout(Request $request)
    {
        // Menghapus token JWT
        JWTAuth::invalidate($request->cookie('token'));

        // Mengembalikan respons dengan pesan logout sukses
        return response()->json([
            'message' => 'Berhasil logout'
        ], Response::HTTP_OK)->withoutCookie('token'); // Menghapus cookie token
    }


}
