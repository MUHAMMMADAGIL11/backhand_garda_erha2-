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
        Schema::create('transaksimasuk', function (Blueprint $table) {
            $table->increments('id_transaksi_masuk');
            $table->integer('id_transaksi')->unique('transaksimasuk_id_transaksi_key');
            $table->string('supplier', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksimasuk');
    }
};
