<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->enum('type', ['pekerjaan_pipa_dinas', 'pekerjaan_pipa_instalasi']);
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->decimal('item_amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};
