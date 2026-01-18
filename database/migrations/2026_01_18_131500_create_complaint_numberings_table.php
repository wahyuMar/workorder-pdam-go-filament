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
        Schema::create('complaint_numberings', function (Blueprint $table) {
            $table->id();
            $table->string('prefix')->default('PGD');
            $table->unsignedInteger('last_number')->default(0);
            $table->date('last_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_numberings');
    }
};
