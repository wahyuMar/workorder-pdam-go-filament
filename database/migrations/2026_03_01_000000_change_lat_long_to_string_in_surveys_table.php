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
        Schema::table('surveys', function (Blueprint $table) {
            $table->string('lokasi_pipa_distribusi_lat')->nullable()->change();
            $table->string('lokasi_pipa_distribusi_long')->nullable()->change();
            $table->string('lokasi_sr_lat')->nullable()->change();
            $table->string('lokasi_sr_long')->nullable()->change();
            $table->string('lokasi_rabatan_lat')->nullable()->change();
            $table->string('lokasi_rabatan_long')->nullable()->change();
            $table->string('lokasi_crossing_lat')->nullable()->change();
            $table->string('lokasi_crossing_long')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->decimal('lokasi_pipa_distribusi_lat', 10, 8)->nullable()->change();
            $table->decimal('lokasi_pipa_distribusi_long', 11, 8)->nullable()->change();
            $table->decimal('lokasi_sr_lat', 10, 8)->nullable()->change();
            $table->decimal('lokasi_sr_long', 11, 8)->nullable()->change();
            $table->decimal('lokasi_rabatan_lat', 10, 8)->nullable()->change();
            $table->decimal('lokasi_rabatan_long', 11, 8)->nullable()->change();
            $table->decimal('lokasi_crossing_lat', 10, 8)->nullable()->change();
            $table->decimal('lokasi_crossing_long', 11, 8)->nullable()->change();
        });
    }
};
