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
        Schema::create('permintaanbarang', function (Blueprint $table) {
            $table->increments('id_permintaan');
            $table->integer('id_user');
            $table->integer('id_barang');
            $table->integer('jumlah_diminta');
            $table->string('status', 50)->default('Menunggu Persetujuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaanbarang');
    }
};
