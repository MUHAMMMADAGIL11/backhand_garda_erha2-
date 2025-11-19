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
        Schema::create('barang', function (Blueprint $table) {
            $table->increments('id_barang');
            $table->integer('id_kategori');
            $table->string('kode_barang', 50)->unique('barang_kode_barang_key');
            $table->string('nama_barang');
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
