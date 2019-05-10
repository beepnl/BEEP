<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHighLowValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('physical_quantities', function (Blueprint $table) 
        {
            if (!Schema::hasColumn('physical_quantities','low_value'))
                $table->float('low_value')->nullable();

            if (!Schema::hasColumn('physical_quantities','high_value'))
                $table->float('high_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('physical_quantities', function (Blueprint $table) 
        {
            if (Schema::hasColumn('physical_quantities','low_value'))
                $table->dropColumn('low_value');

            if (Schema::hasColumn('physical_quantities','high_value'))
                $table->dropColumn('high_value');
        });
    }
}
