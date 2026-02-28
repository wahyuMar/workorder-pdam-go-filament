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
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('no_survey')->nullable();

            // Lokasi Pipa Distribusi
            $table->decimal('lokasi_pipa_distribusi_lat', 10, 8)->nullable();
            $table->decimal('lokasi_pipa_distribusi_long', 11, 8)->nullable();

            // Panjang Pipa SR
            $table->integer('panjang_pipa_sr')->nullable();

            // Ukuran Clamp Sadel
            $table->string('ukuran_clamp_sadel')->nullable();

            // Lokasi SR
            $table->decimal('lokasi_sr_lat', 10, 8)->nullable();
            $table->decimal('lokasi_sr_long', 11, 8)->nullable();

            // Foto-foto
            $table->string('foto_rumah')->nullable();
            $table->string('foto_penghuni')->nullable();
            $table->string('foto_lokasi_wm')->nullable();

            // Lokasi Rabatan
            $table->decimal('lokasi_rabatan_lat', 10, 8)->nullable();
            $table->decimal('lokasi_rabatan_long', 11, 8)->nullable();
            $table->integer('panjang_rabatan')->nullable();

            // Lokasi Crossing
            $table->decimal('lokasi_crossing_lat', 10, 8)->nullable();
            $table->decimal('lokasi_crossing_long', 11, 8)->nullable();
            $table->integer('panjang_crossing')->nullable();

            // Jenis Crossing
            $table->string('jenis_crossing')->nullable();

            // Klasifikasi SR
            $table->string('klasifikasi_sr')->nullable();

            // Tanggal Survey
            $table->dateTime('tanggal_survey')->nullable();
            $table->foreignId('customer_registration_id')->constrained('customer_registrations')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
