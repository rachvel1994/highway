<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id');
            $table->string('title');
            $table->integer('quantity')->default(0);
            $table->foreignId('category_id')->nullable();
            $table->foreignId('measure_id')->nullable();
            $table->float('price')->default(0.00);
            $table->text('comment')->nullable();
            $table->unique(['store_id', 'title']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
