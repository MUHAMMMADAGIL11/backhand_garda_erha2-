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
        Schema::table('transaksikeluar', function (Blueprint $table) {
            $table->foreign(['id_transaksi'], 'transaksikeluar_id_transaksi_fkey')->references(['id_transaksi'])->on('transaksi')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksikeluar', function (Blueprint $table) {
            $table->dropForeign('transaksikeluar_id_transaksi_fkey');
        });
    }
};
