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
        Schema::create('work_asset_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_type_id')->nullable();
            $table->foreignId('equipment_id')->nullable();
            $table->foreignId('personal_id')->nullable();
            $table->foreignId('company_item_id')->nullable();
            $table->foreignId('company_id')->nullable();
            $table->foreignId('store_id')->nullable();
            $table->foreignId('store_product_id')->nullable();
            $table->decimal('time_spend')->default(0);
            $table->integer('completed_trip')->default(0);
            $table->decimal('fuel_spend')->default(0);
            $table->decimal('company_item_quantity')->default(0);
            $table->decimal('store_product_quantity')->default(0);
            $table->decimal('store_product_price')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_asset_details');
    }
};
