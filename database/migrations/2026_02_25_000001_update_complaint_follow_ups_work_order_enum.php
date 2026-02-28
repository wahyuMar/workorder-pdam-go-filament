<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to modify the enum column
        DB::statement("ALTER TABLE complaint_follow_ups CHANGE work_order work_order ENUM('Ganti Meter', 'Tutup', 'Buka Kembali', 'Cabut', 'Ubah Nama', 'Ganti Alamat', 'Ganti Tarif') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the original enum values
        DB::statement("ALTER TABLE complaint_follow_ups CHANGE work_order work_order ENUM('Ganti Meter', 'Tutup', 'Buka Kembali', 'Cabut', 'Ganti Nama', 'Ganti Alamat', 'Ganti Tarif') NULL");
    }
};
