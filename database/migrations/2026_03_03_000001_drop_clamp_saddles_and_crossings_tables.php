<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('clamp_saddles');
        Schema::dropIfExists('crossings');
    }

    public function down(): void
    {
        // Tidak di-restore karena data sudah dimigrasikan ke material_and_services
    }
};
