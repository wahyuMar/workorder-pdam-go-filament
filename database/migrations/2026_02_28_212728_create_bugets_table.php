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
        Schema::create('bugets', function (Blueprint $table) {
            $table->id();
            $table->string('budgeting_number')->nullable();
            $table->foreignId('survey_id')->constrained('surveys')->noActionOnDelete();
            $table->date('date');
            $table->string('blueprint', 255)->nullable();
            $table->foreignId('created_by')->constrained('users')->noActionOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bugets');
    }
};
