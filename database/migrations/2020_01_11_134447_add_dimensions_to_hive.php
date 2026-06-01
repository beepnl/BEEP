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
        Schema::table('hives', function (Blueprint $table) {
            $table->decimal('bb_width_cm', 6, 1)->unsigned()->nullable();
            $table->decimal('bb_depth_cm', 6, 1)->unsigned()->nullable();
            $table->decimal('bb_height_cm', 6, 1)->unsigned()->nullable();
            $table->decimal('fr_width_cm', 6, 1)->unsigned()->nullable();
            $table->decimal('fr_height_cm', 6, 1)->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hives', function (Blueprint $table) {
            $table->dropColumn('bb_width_cm');
            $table->dropColumn('bb_depth_cm');
            $table->dropColumn('bb_height_cm');
            $table->dropColumn('fr_width_cm');
            $table->dropColumn('fr_height_cm');
        });
    }
};
