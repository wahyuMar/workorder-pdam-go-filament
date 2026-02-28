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
        Schema::create('meter_name_changes', function (Blueprint $table) {
            $table->id();
            $table->string('no_spun')->unique();
            $table->foreignId('complaint_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('pegawai_id')->nullable();
            $table->string('nama_pegawai')->nullable();
            $table->string('no_sambungan')->nullable();
            $table->string('nama_lama')->nullable();
            $table->string('nama_baru')->nullable();
            $table->text('alamat_lama')->nullable();
            $table->text('alamat_baru')->nullable();
            $table->string('email_lama')->nullable();
            $table->string('email_baru')->nullable();
            $table->string('no_hp_lama')->nullable();
            $table->string('no_hp_baru')->nullable();
            $table->string('no_ktp_lama')->nullable();
            $table->string('no_ktp_baru')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->text('alasan_ubah_nama')->nullable();
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
        Schema::dropIfExists('meter_name_changes');
    }
};
