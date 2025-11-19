<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class UserService
{
    // Register a new user
   public function register(array $data)
{
    // Validate input data
    $validator = Validator::make($data, [
        'username' => 'required|string|max:50|unique:users,username',
        'password' => 'required|string|min:6',
        'nama_lengkap' => 'nullable|string|max:255',
        'role' => 'nullable|string|max:255',
    ]);

    // Throw an exception if validation fails
    if ($validator->fails()) {
        throw new ValidationException($validator);
    }

    // Normalize role sebelum menyimpan
    $normalizedRole = User::normalizeRole($data['role'] ?? null);

    // Create and return the new user
    return User::create([
        'username' => $data['username'],
        'password' => Hash::make($data['password']),  // Hash the password before saving
        'nama_lengkap' => $data['nama_lengkap'] ?? null,
        'is_active' => true,  // Default value
        'role' => $normalizedRole,
    ]);
}

}
