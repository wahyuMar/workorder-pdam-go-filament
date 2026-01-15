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
        Schema::table('customer_registrations', function (Blueprint $table) {
            $table->dropColumn('program');
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete()->after('nama_lengkap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_registrations', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
            $table->string('program')->nullable()->after('nama_lengkap');
        });
    }
};
