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
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['store_id', 'title']);
            $table->timestamp('buy_at')->nullable();
            $table->timestamp('sold_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unique(['store_id', 'title']);
            $table->dropColumn('buy_at');
            $table->dropColumn('sold_at');
        });
    }
};
