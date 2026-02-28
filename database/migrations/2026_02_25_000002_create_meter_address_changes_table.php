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
        Schema::create('meter_address_changes', function (Blueprint $table) {
            $table->id();
            $table->string('no_spua')->unique();
            $table->foreignId('complaint_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('pegawai_id')->nullable();
            $table->string('nama_pegawai')->nullable();
            $table->string('no_sambungan')->nullable();
            $table->string('nama')->nullable();
            $table->unsignedBigInteger('id_unit_lama')->nullable();
            $table->unsignedBigInteger('id_desa_lama')->nullable();
            $table->unsignedBigInteger('id_rt_rw_lama')->nullable();
            $table->unsignedBigInteger('id_wilayah_lama')->nullable();
            $table->unsignedBigInteger('id_jalan_lama')->nullable();
            $table->unsignedBigInteger('id_kolektor_lama')->nullable();
            $table->unsignedBigInteger('id_unit_baru')->nullable();
            $table->unsignedBigInteger('id_desa_baru')->nullable();
            $table->unsignedBigInteger('id_rt_rw_baru')->nullable();
            $table->unsignedBigInteger('id_wilayah_baru')->nullable();
            $table->unsignedBigInteger('id_jalan_baru')->nullable();
            $table->unsignedBigInteger('id_kolektor_baru')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->decimal('biaya_ubah_alamat', 10, 2)->nullable();
            $table->text('alasan_ubah_alamat')->nullable();
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
        Schema::dropIfExists('meter_address_changes');
    }
};
