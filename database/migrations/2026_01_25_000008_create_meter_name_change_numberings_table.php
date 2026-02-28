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
        Schema::create('meter_name_change_numberings', function (Blueprint $table) {
            $table->id();
            $table->string('prefix')->default('SPUN');
            $table->integer('last_number')->default(0);
            $table->date('last_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_name_change_numberings');
    }
};
