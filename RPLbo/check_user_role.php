<?php

/**
 * Script untuk mengecek dan memperbaiki role user di database
 * 
 * Cara menggunakan:
 * 1. Buka terminal di folder project
 * 2. Jalankan: php check_user_role.php
 * 3. Atau jalankan: php artisan tinker
 *    Lalu ketik: DB::table('users')->get(['id_user', 'username', 'role']);
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK ROLE USER DI DATABASE ===\n\n";

$users = DB::table('users')->select('id_user', 'username', 'role', 'nama_lengkap')->get();

if ($users->isEmpty()) {
    echo "Tidak ada user di database.\n";
    exit;
}

echo "Daftar User:\n";
echo str_repeat("-", 80) . "\n";
printf("%-5s | %-20s | %-25s | %-20s\n", "ID", "Username", "Role", "Nama Lengkap");
echo str_repeat("-", 80) . "\n";

foreach ($users as $user) {
    $role = $user->role ?? 'NULL';
    if ($role === 'NULL' || $role === '') {
        $role = '⚠️ NULL/KOSONG';
    }
    printf("%-5s | %-20s | %-25s | %-20s\n", 
        $user->id_user, 
        $user->username, 
        $role,
        $user->nama_lengkap ?? '-'
    );
}

echo str_repeat("-", 80) . "\n\n";

// Cek user yang tidak punya role
$usersWithoutRole = DB::table('users')
    ->whereNull('role')
    ->orWhere('role', '')
    ->get();

if ($usersWithoutRole->isNotEmpty()) {
    echo "⚠️  PERINGATAN: Ada " . $usersWithoutRole->count() . " user yang tidak memiliki role!\n\n";
    echo "Untuk memperbaiki, jalankan query SQL berikut:\n";
    echo "UPDATE users SET role = 'AdminGudang' WHERE username = 'USERNAME_ANDA';\n\n";
    
    echo "Atau gunakan Tinker:\n";
    echo "php artisan tinker\n";
    echo "DB::table('users')->where('username', 'USERNAME_ANDA')->update(['role' => 'AdminGudang']);\n\n";
}

echo "=== SELESAI ===\n";

