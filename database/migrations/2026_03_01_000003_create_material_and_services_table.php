<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_and_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', [
                'clamp_saddle',
                'crossing',
                'material',
                'material_dinas',
            ]);
            $table->string('unit')->nullable();
            $table->boolean('is_deletable')->default(true);
            $table->boolean('is_service')->default(false);
            $table->decimal('price', 15, 2)->default(0);
            $table->timestamps();
        });

        // Migrate clamp_saddles data
        if (Schema::hasTable('clamp_saddles')) {
            DB::table('clamp_saddles')->orderBy('id')->each(function ($row) {
                DB::table('material_and_services')->insert([
                    'id'           => null,
                    'name'         => $row->name . (isset($row->brand) ? ' (' . $row->brand . ')' : ''),
                    'category'     => 'clamp_saddle',
                    'unit'         => 'pcs',
                    'is_deletable' => true,
                    'is_service'   => false,
                    'price'        => $row->price,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            });
        }

        // Migrate crossings data
        if (Schema::hasTable('crossings')) {
            DB::table('crossings')->orderBy('id')->each(function ($row) {
                DB::table('material_and_services')->insert([
                    'id'           => null,
                    'name'         => $row->name,
                    'category'     => 'crossing',
                    'unit'         => 'meter',
                    'is_deletable' => true,
                    'is_service'   => true,
                    'price'        => $row->price,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('material_and_services');
    }
};
