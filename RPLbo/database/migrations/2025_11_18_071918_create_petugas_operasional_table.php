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
        Schema::create('petugas_operasional', function (Blueprint $table) {
            $table->increments('id_petugas_operasional');
            $table->integer('id_user')->unique('petugas_operasional_id_user_key');
            $table->string('divisi', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petugas_operasional');
    }
};
