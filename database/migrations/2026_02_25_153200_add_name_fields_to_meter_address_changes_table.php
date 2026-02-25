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
        Schema::table('meter_address_changes', function (Blueprint $table) {
            // Old address names
            $table->string('nama_unit_lama')->nullable()->after('id_unit_lama');
            $table->string('nama_desa_lama')->nullable()->after('id_desa_lama');
            $table->string('nama_wilayah_lama')->nullable()->after('id_wilayah_lama');
            $table->string('nama_jalan_lama')->nullable()->after('id_jalan_lama');
            $table->string('nama_rt_rw_lama')->nullable()->after('id_rt_rw_lama');
            $table->string('nama_kolektor_lama')->nullable()->after('id_kolektor_lama');
            
            // New address names
            $table->string('nama_unit_baru')->nullable()->after('id_unit_baru');
            $table->string('nama_desa_baru')->nullable()->after('id_desa_baru');
            $table->string('nama_wilayah_baru')->nullable()->after('id_wilayah_baru');
            $table->string('nama_jalan_baru')->nullable()->after('id_jalan_baru');
            $table->string('nama_rt_rw_baru')->nullable()->after('id_rt_rw_baru');
            $table->string('nama_kolektor_baru')->nullable()->after('id_kolektor_baru');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meter_address_changes', function (Blueprint $table) {
            $table->dropColumn([
                'nama_unit_lama', 'nama_desa_lama', 'nama_wilayah_lama', 'nama_jalan_lama', 'nama_rt_rw_lama', 'nama_kolektor_lama',
                'nama_unit_baru', 'nama_desa_baru', 'nama_wilayah_baru', 'nama_jalan_baru', 'nama_rt_rw_baru', 'nama_kolektor_baru',
            ]);
        });
    }
};
