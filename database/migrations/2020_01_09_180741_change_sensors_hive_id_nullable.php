<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSensorsHiveIdNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sensors', function (Blueprint $table) {
            if (Schema::hasColumn('sensors','hive_id'))
            {
                $table->integer('hive_id')->unsigned()->nullable()->change();;
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sensors', function (Blueprint $table) {
            if (Schema::hasColumn('sensors','hive_id'))
            {
                $table->integer('hive_id')->unsigned()->change();;
            }
        });
    }
}
