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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('no_pengaduan')->unique()->nullable(); // Auto generated
            $table->foreignId('complaint_type_id')->nullable()->constrained('complaint_types')->onDelete('set null');
            $table->string('no_sambungan')->nullable();
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('no_ktp')->nullable();
            $table->string('sumber')->nullable(); // Sumber pengaduan
            $table->string('judul_pengaduan');
            $table->text('isi_pengaduan');
            $table->json('foto')->nullable(); // Multiple photos
            $table->dateTime('tanggal')->nullable();
            $table->string('status')->default('pending');
            $table->string('priority')->default('medium');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
