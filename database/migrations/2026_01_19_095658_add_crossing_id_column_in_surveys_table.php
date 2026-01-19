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
            $table->dropColumn('jenis_crossing');
            $table->foreignId('clamp_saddle_id')->nullable()->constrained('clamp_saddles')->after('panjang_rabatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropForeign(['clamp_saddle_id']);
            $table->dropColumn('clamp_saddle_id');
        });
    }
};
