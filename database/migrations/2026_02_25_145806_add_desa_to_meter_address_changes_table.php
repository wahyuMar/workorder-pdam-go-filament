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
            $table->unsignedBigInteger('id_desa_lama')->nullable()->after('id_unit_lama')->change();
            $table->unsignedBigInteger('id_desa_baru')->nullable()->after('id_unit_baru')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meter_address_changes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_desa_lama')->nullable()->after('id_unit_lama')->change();
            $table->unsignedBigInteger('id_desa_baru')->nullable()->after('id_unit_baru')->change();
        });
    }
};
