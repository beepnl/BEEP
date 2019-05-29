<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMinMaxValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('measurements', function (Blueprint $table) {
            if (!Schema::hasColumn('measurements','min_value'))
                $table->float('min_value')->nullable();
            if (!Schema::hasColumn('measurements','max_value'))
                $table->float('max_value')->nullable();
            if (!Schema::hasColumn('measurements','hex_color'))
                $table->string('hex_color', 6)->nullable()->default('333333');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('measurements', function (Blueprint $table) {
            if (Schema::hasColumn('measurements','min_value'))
                $table->dropColumn('min_value');

            if (Schema::hasColumn('measurements','max_value'))
                $table->dropColumn('max_value');

            if (Schema::hasColumn('measurements','hex_color'))
                $table->dropColumn('hex_color');
            
        });
    }
}
