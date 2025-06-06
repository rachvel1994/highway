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
        Schema::create('damages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id');
            $table->string('damage', 800)->nullable();
            $table->string('detail_name', 800)->nullable();
            $table->integer('quantity')->default(0);
            $table->float('detail_price', 2)->default(0.00);
            $table->float('craft_price', 2)->default(0.00);
            $table->string('craftsman')->nullable();
            $table->float('additional_expense', 2)->default(0.00);
            $table->decimal('total_price', 10)->default(0);
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damages');
    }
};
