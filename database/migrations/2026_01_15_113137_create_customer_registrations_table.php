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
        Schema::create('customer_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('no_surat')->nullable();
            $table->string('nama_lengkap')->nullable();
            $table->string('program')->nullable();
            $table->string('no_ktp')->nullable();
            $table->string('no_kk')->nullable();
            $table->text('alamat_ktp')->nullable();
            $table->string('dusun_kampung_ktp')->nullable();
            $table->integer('rt_ktp')->nullable();
            $table->integer('rw_ktp')->nullable();
            $table->string('kel_desa_ktp')->nullable();
            $table->string('kecamatan_ktp')->nullable();
            $table->string('kab_kota_ktp')->nullable();
            $table->text('pekerjaan')->nullable();
            $table->string('email')->nullable();
            $table->string('no_telp')->nullable();
            $table->string('no_hp')->nullable();
            $table->text('alamat_pasang')->nullable();
            $table->string('dusun_kampung_pasang')->nullable();
            $table->integer('rt_pasang')->nullable();
            $table->integer('rw_pasang')->nullable();
            $table->string('kel_desa_pasang')->nullable();
            $table->string('kecamatan_pasang')->nullable();
            $table->string('kab_kota_pasang')->nullable();
            $table->integer('jumlah_penghuni_tetap')->nullable();
            $table->integer('jumlah_penghuni_tidak_tetap')->nullable();
            $table->integer('jumlah_kran_air_minum')->nullable();
            $table->string('jenis_rumah')->nullable();
            $table->integer('jumlah_kran')->nullable();
            $table->integer('daya_listrik')->nullable();
            $table->string('upload_ktp')->nullable();
            $table->string('upload_kk')->nullable();
            $table->string('upload_tagihan_listrik')->nullable();
            $table->string('upload_foto_rumah')->nullable();
            $table->string('lang')->nullable();
            $table->string('lat')->nullable();
            $table->dateTime('tanggal')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_registrations');
    }
};
