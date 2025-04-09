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
            $table->foreignId('work_asset_id')->constrained()->onDelete('cascade'); // main model relation
            $table->foreignId('job_type_id')->nullable();

            // ტექნიკა
            $table->foreignId('equipment_id')->nullable();
            $table->decimal('time_spend', 10, 2)->default(0);
            $table->integer('completed_trip')->default(0);

            // საწვავი
            $table->foreignId('fuel_id')->nullable();
            $table->decimal('fuel_price', 10, 2)->default(0);
            $table->decimal('fuel_spend', 10, 2)->default(0);
            $table->decimal('fuel_total_price', 10, 2)->default(0);

            // კომპანია
            $table->foreignId('company_id')->nullable();
            $table->foreignId('item_id')->nullable();
            $table->decimal('item_price', 10, 2)->default(0);
            $table->decimal('item_quantity', 10, 2)->default(0);
            $table->decimal('item_total_price', 10, 2)->default(0);

            // მაღაზია
            $table->foreignId('store_id')->nullable();
            $table->foreignId('store_product_id')->nullable();
            $table->decimal('product_price', 10, 2)->default(0);
            $table->decimal('product_quantity', 10, 2)->default(0);
            $table->decimal('product_price_total', 10, 2)->default(0);

            // პერსონალი
            $table->foreignId('personal_id')->nullable();
            $table->decimal('person_salary', 10, 2)->default(0);
            $table->tinyInteger('person_salary_type')->default(1);
            $table->decimal('person_worked_days', 10, 2)->default(0);
            $table->decimal('person_worked_quantity', 10, 2)->default(0);
            $table->decimal('person_salary_total', 10, 2)->default(0);

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
