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
        Schema::table('work_assets', function (Blueprint $table) {
            $table->decimal('damage_share_total', 10, 2)->default(0)->after('grand_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_assets', function (Blueprint $table) {
            $table->dropColumn('damage_share_total');
        });
    }
};
