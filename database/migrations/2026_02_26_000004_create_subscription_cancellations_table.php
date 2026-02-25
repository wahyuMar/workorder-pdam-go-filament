<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_cancellations', function (Blueprint $table) {
            $table->id();
            $table->string('no_bacl')->unique();
            $table->foreignId('complaint_id')->constrained('complaints')->onDelete('cascade');
            $table->string('no_sambungan');
            $table->string('nama');
            $table->text('alamat');
            $table->string('lokasi')->nullable();
            $table->string('foto_sebelum')->nullable();
            $table->string('foto_sesudah')->nullable();
            $table->text('catatan')->nullable();
            $table->dateTime('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_cancellations');
    }
};
