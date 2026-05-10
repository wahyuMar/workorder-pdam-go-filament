<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'meter_repairs',
            'meter_replacements',
            'meter_closeds',
            'meter_reopenings',
            'meter_disconnections',
            'meter_teras',
        ] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->boolean('is_confirmed')->default(false)->after('tanggal');
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'meter_repairs',
            'meter_replacements',
            'meter_closeds',
            'meter_reopenings',
            'meter_disconnections',
            'meter_teras',
        ] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn('is_confirmed');
            });
        }
    }
};