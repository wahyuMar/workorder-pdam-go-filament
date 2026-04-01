<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE complaint_follow_ups CHANGE work_order work_order ENUM('Ganti Meter', 'Tutup', 'Buka Kembali', 'Cabut', 'Ubah Nama', 'Ganti Alamat', 'Ganti Tarif', 'Perbaikan', 'Tera Meter') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE complaint_follow_ups CHANGE work_order work_order ENUM('Ganti Meter', 'Tutup', 'Buka Kembali', 'Cabut', 'Ubah Nama', 'Ganti Alamat', 'Ganti Tarif') NULL");
    }
};
