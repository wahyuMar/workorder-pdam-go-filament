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
            // Foreign keys untuk alamat KTP
            $table->foreignId('province_id_ktp')->nullable()->after('kab_kota_ktp')->constrained('provinces')->nullOnDelete();
            $table->foreignId('regency_id_ktp')->nullable()->after('province_id_ktp')->constrained('regencies')->nullOnDelete();
            $table->foreignId('district_id_ktp')->nullable()->after('regency_id_ktp')->constrained('districts')->nullOnDelete();
            $table->foreignId('village_id_ktp')->nullable()->after('district_id_ktp')->constrained('villages')->nullOnDelete();

            // Foreign keys untuk alamat pasang
            $table->foreignId('province_id_pasang')->nullable()->after('kab_kota_pasang')->constrained('provinces')->nullOnDelete();
            $table->foreignId('regency_id_pasang')->nullable()->after('province_id_pasang')->constrained('regencies')->nullOnDelete();
            $table->foreignId('district_id_pasang')->nullable()->after('regency_id_pasang')->constrained('districts')->nullOnDelete();
            $table->foreignId('village_id_pasang')->nullable()->after('district_id_pasang')->constrained('villages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_registrations', function (Blueprint $table) {
            $table->dropForeign(['province_id_ktp']);
            $table->dropForeign(['regency_id_ktp']);
            $table->dropForeign(['district_id_ktp']);
            $table->dropForeign(['village_id_ktp']);
            $table->dropForeign(['province_id_pasang']);
            $table->dropForeign(['regency_id_pasang']);
            $table->dropForeign(['district_id_pasang']);
            $table->dropForeign(['village_id_pasang']);

            $table->dropColumn([
                'province_id_ktp',
                'regency_id_ktp',
                'district_id_ktp',
                'village_id_ktp',
                'province_id_pasang',
                'regency_id_pasang',
                'district_id_pasang',
                'village_id_pasang',
            ]);
        });
    }
};
