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
        Schema::create('meter_rate_changes', function (Blueprint $table) {
            $table->id();
            $table->string('no_sput')->unique();
            $table->foreignId('complaint_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('pegawai_id')->nullable();
            $table->string('nama_pegawai')->nullable();
            $table->string('no_sambungan')->nullable();
            $table->string('nama')->nullable();
            $table->string('tarif_lama')->nullable();
            $table->string('tarif_baru')->nullable();
            $table->text('alamat')->nullable();
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->text('alasan_ganti_tarif')->nullable();
            $table->json('upload_ktp')->nullable();
            $table->json('upload_kk')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->dateTime('tanggal')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_rate_changes');
    }
};
