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
        Schema::create('meter_replacements', function (Blueprint $table) {
            $table->id();
            $table->string('no_spgm')->unique()->nullable(); // Auto generated
            $table->foreignId('complaint_id')->constrained('complaints')->onDelete('cascade');
            $table->integer('pegawai_id')->nullable(); // Pegawai
            $table->string('nama_pegawai')->nullable();
            $table->string('no_sambungan');
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('alasan_penggantian')->nullable();
            $table->decimal('biaya_ganti_meter', 15, 2)->nullable();
            $table->dateTime('tanggal')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_replacements');
    }
};
