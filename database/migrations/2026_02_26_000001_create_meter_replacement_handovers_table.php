<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_replacement_handovers', function (Blueprint $table) {
            $table->id();
            $table->string('no_bast_gm')->unique();
            $table->foreignId('complaint_id')->constrained('complaints')->onDelete('cascade');
            $table->string('no_sambungan');
            $table->string('nama');
            $table->text('alamat');
            $table->string('lokasi')->nullable();
            $table->string('foto_sebelum')->nullable();
            $table->string('foto_sesudah')->nullable();
            $table->string('merk_wm_lama')->nullable();
            $table->string('no_wm_lama')->nullable();
            $table->string('merk_wm_baru')->nullable();
            $table->string('no_wm_baru')->nullable();
            $table->dateTime('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_replacement_handovers');
    }
};
