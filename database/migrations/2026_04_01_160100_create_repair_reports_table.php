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
        Schema::create('repair_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('no_bap')->unique();
            $table->foreignId('complaint_id')->constrained('complaints')->cascadeOnDelete();
            $table->string('no_sambungan');
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('foto_sebelum')->nullable();
            $table->string('foto_sesudah')->nullable();
            $table->json('items')->nullable();
            $table->text('catatan')->nullable();
            $table->dateTime('tanggal');
            $table->timestamps();

            $table->unique('complaint_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_reports');
    }
};
