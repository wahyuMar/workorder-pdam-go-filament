<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_items', function (Blueprint $table) {
            $table->renameColumn('type', 'category');
            $table->enum('sub_category', [
                'pekerjaan_tanah_dinas',
                'material_pipa_dan_acc_dinas',
                'jasa_pasang_pipa_dan_acc_dinas',
                'lain_lain_dinas',
                'pekerjaan_tanah_instalasi',
                'material_pipa_dan_acc_instalasi',
                'jasa_pasang_pipa_dan_acc_instalasi',
                'lain_lain_instalasi',
            ])->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('budget_items', function (Blueprint $table) {
            $table->renameColumn('category', 'type');
            $table->dropColumn('sub_category');
        });
    }
};
