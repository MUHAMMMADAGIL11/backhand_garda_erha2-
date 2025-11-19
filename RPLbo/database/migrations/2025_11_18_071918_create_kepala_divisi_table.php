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
        Schema::create('kepala_divisi', function (Blueprint $table) {
            $table->increments('id_kepala_divisi');
            $table->integer('id_user')->unique('kepala_divisi_id_user_key');
            $table->string('divisi', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kepala_divisi');
    }
};
