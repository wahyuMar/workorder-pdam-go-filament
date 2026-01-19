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
        Schema::create('complaint_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained('complaints')->onDelete('cascade');
            $table->string('complaint_number')->nullable();
            $table->text('carbon_copies')->nullable();
            $table->enum('work_order', [
                'Ganti Meter',
                'Tutup',
                'Buka Kembali',
                'Cabut',
                'Ganti Nama',
                'Ganti Alamat',
                'Ganti Tarif',
            ])->nullable();
            $table->text('notes')->nullable();
            $table->text('photos')->nullable();
            $table->dateTime('follow_up_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_follow_ups');
    }
};
