<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Hive;

class CorrectHiveBBDepthAndHeight extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hives', function (Blueprint $table) 
        {
            $hives_bb_error = Hive::withTrashed()->whereRaw('bb_depth_cm < bb_height_cm')->get();
            $count = 0;
            if (count($hives_bb_error) > 0)
            {
                foreach ($hives_bb_error as $hive) // replace bb_height_cm with bb_depth_cm
                {
                    $bb_depth_temp      = $hive->bb_height_cm;
                    $hive->bb_height_cm = $hive->bb_depth_cm; 
                    $hive->bb_depth_cm  = $bb_depth_temp;
                    if ($hive->save())
                        $count++;
                }
            }
            echo("Corrected $count hives where bb_depth_cm < bb_height_cm");
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
            //
        });
    }
}
