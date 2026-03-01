<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->foreignId('material_clamp_saddle_id')
                ->nullable()
                ->after('clamp_saddle_id')
                ->constrained('material_and_services')
                ->nullOnDelete();

            $table->foreignId('material_crossing_id')
                ->nullable()
                ->after('crossing_id')
                ->constrained('material_and_services')
                ->nullOnDelete();
        });

        // Drop old FK columns
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropForeign(['clamp_saddle_id']);
            $table->dropColumn(['clamp_saddle_id', 'clamp_saddle_price']);
            $table->dropForeign(['crossing_id']);
            $table->dropColumn('crossing_id');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropForeign(['material_clamp_saddle_id']);
            $table->dropColumn('material_clamp_saddle_id');
            $table->dropForeign(['material_crossing_id']);
            $table->dropColumn('material_crossing_id');
        });
    }
};
