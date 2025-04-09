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
        Schema::create('company_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id');
            $table->string('title');
            $table->integer('quantity')->default(0);
            $table->foreignId('category_id')->nullable();
            $table->foreignId('measure_id')->nullable();
            $table->float('price')->default(0.00);
            $table->text('comment')->nullable();
            $table->decimal('total_price')->default(0)->nullable();
            $table->unique(['company_id', 'title']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_items');
    }
};
