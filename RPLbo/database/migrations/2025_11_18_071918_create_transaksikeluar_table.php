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
        Schema::create('transaksikeluar', function (Blueprint $table) {
            $table->increments('id_transaksi_keluar');
            $table->integer('id_transaksi')->unique('transaksikeluar_id_transaksi_key');
            $table->string('tujuan', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksikeluar');
    }
};
