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
        Schema::create('work_assets', function (Blueprint $table) {
            $table->id();
            $table->string('street');
            $table->foreignId('equipment_id')->nullable();
            $table->foreignId('personal_id')->nullable();
            $table->foreignId('company_id')->nullable();
            $table->foreignId('job_type_id')->nullable();
            $table->foreignId('measure_id')->nullable();
            $table->string('traveled_km')->nullable();
            $table->time('time_spend')->nullable();
            $table->string('fuel_spend')->nullable();
            $table->text('failure')->nullable();
            $table->text('taken_items')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_assets');
    }
};
