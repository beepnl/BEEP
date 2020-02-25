<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDimensionsToHive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hives', function (Blueprint $table) {
            $table->dropColumn('bb_width_cm');
            $table->dropColumn('bb_depth_cm');
            $table->dropColumn('bb_height_cm');
            $table->dropColumn('fr_width_cm');
            $table->dropColumn('fr_height_cm');
        });
    }
}
