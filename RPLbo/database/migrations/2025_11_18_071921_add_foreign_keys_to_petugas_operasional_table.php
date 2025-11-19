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
        Schema::table('petugas_operasional', function (Blueprint $table) {
            $table->foreign(['id_user'], 'petugas_operasional_id_user_fkey')->references(['id_user'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petugas_operasional', function (Blueprint $table) {
            $table->dropForeign('petugas_operasional_id_user_fkey');
        });
    }
};
