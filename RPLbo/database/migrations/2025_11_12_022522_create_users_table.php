<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('username', 50)->unique('users_username_key');
            $table->string('password');
            $table->string('nama_lengkap');
            $table->enum('role', ['AdminGudang', 'PetugasOperasional', 'KepalaDivisi']);
            $table->boolean('is_aktif')->default(true);
            $table->string('divisi', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
