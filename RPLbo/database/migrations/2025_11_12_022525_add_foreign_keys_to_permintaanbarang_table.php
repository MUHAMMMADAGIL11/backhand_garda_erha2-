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
        Schema::table('permintaanbarang', function (Blueprint $table) {
            $table->foreign(['id_barang'], 'permintaanbarang_id_barang_fkey')->references(['id_barang'])->on('barang')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['id_user'], 'permintaanbarang_id_user_fkey')->references(['id_user'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permintaanbarang', function (Blueprint $table) {
            $table->dropForeign('permintaanbarang_id_barang_fkey');
            $table->dropForeign('permintaanbarang_id_user_fkey');
        });
    }
};
