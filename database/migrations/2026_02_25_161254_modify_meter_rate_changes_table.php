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
        Schema::table('meter_rate_changes', function (Blueprint $table) {
            // Drop columns not needed
            $table->dropColumn([
                'pegawai_id',
                'nama_pegawai',
                'tarif_lama',
                'tarif_baru',
                'latitude',
                'longitude',
                'upload_ktp',
                'upload_kk',
            ]);
            
            // Add no_ktp column
            $table->string('no_ktp')->nullable()->after('no_hp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meter_rate_changes', function (Blueprint $table) {
            // Add back dropped columns
            $table->unsignedBigInteger('pegawai_id')->nullable();
            $table->string('nama_pegawai')->nullable();
            $table->string('tarif_lama')->nullable();
            $table->string('tarif_baru')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->json('upload_ktp')->nullable();
            $table->json('upload_kk')->nullable();
            
            // Drop no_ktp
            $table->dropColumn('no_ktp');
        });
    }
};
