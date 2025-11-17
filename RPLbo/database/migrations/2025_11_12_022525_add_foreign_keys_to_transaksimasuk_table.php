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
        Schema::table('transaksimasuk', function (Blueprint $table) {
            $table->foreign(['id_transaksi'], 'transaksimasuk_id_transaksi_fkey')->references(['id_transaksi'])->on('transaksi')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksimasuk', function (Blueprint $table) {
            $table->dropForeign('transaksimasuk_id_transaksi_fkey');
        });
    }
};
