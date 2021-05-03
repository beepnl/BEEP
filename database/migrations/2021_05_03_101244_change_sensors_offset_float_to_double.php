<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSensorsOffsetFloatToDouble extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('sensors', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sensors` MODIFY COLUMN `datetime_offset_sec` DOUBLE NULL;");
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
            $table->float('datetime_offset_sec')->change();
        });
    }
}
