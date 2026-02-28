<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_changes', function (Blueprint $table) {
            $table->id();
            $table->string('no_baus')->unique();
            $table->foreignId('complaint_id')->constrained('complaints')->onDelete('cascade');
            $table->string('no_sambungan');
            $table->string('nama');
            $table->text('alamat');
            $table->string('lokasi')->nullable();
            $table->string('jenis_rumah')->nullable();
            $table->integer('jumlah_kran')->nullable();
            $table->integer('daya_listrik')->nullable();
            $table->string('verifikasi_ktp')->nullable();
            $table->string('verifikasi_kk')->nullable();
            $table->string('verifikasi_tagihan_listrik')->nullable();
            $table->string('verifikasi_foto_rumah')->nullable();
            $table->string('klasifikasi_sr')->nullable();
            $table->text('catatan')->nullable();
            $table->dateTime('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_changes');
    }
};
