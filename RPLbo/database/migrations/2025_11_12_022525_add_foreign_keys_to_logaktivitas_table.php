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
        Schema::table('logaktivitas', function (Blueprint $table) {
            $table->foreign(['id_user'], 'logaktivitas_id_user_fkey')->references(['id_user'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logaktivitas', function (Blueprint $table) {
            $table->dropForeign('logaktivitas_id_user_fkey');
        });
    }
};
