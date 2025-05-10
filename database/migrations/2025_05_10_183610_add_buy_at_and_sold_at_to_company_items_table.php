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
        Schema::table('company_items', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'title']);
            $table->timestamp('buy_at')->nullable();
            $table->timestamp('sold_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_items', function (Blueprint $table) {
            $table->unique(['company_id', 'title']);
            $table->dropColumn('buy_at');
            $table->dropColumn('sold_at');
        });
    }
};
