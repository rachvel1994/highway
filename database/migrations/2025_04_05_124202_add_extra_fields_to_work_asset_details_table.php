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
        Schema::table('work_asset_details', function (Blueprint $table) {
            $table->decimal('person_salary')->default(0)->nullable()->after('store_product_price');
            $table->integer('person_salary_type')->default(1)->nullable()->after('person_salary');
            $table->integer('person_worked_days')->default(0)->nullable()->after('person_salary_type');
            $table->integer('person_worked_quantity')->default(0)->nullable()->after('person_worked_days');
            $table->decimal('person_salary_total')->default(0)->nullable()->after('person_worked_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_asset_details', function (Blueprint $table) {
            $table->dropColumn([
                'person_salary',
                'person_salary_type',
                'person_worked_days',
                'person_worked_quantity',
                'person_salary_total',
            ]);
        });
    }
};
