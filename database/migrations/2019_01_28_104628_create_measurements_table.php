<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMeasurementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('measurements')) 
        {
            Schema::create('measurements', function (Blueprint $table) 
            {
                $table->increments('id');
                $table->string('abbreviation');
                $table->boolean('show_in_charts')->default(true);
                $table->integer('chart_group')->unsigned()->default(1);
                $table->integer('physical_quantity_id')->unsigned();

                $table->foreign('physical_quantity_id')->references('id')->on('physical_quantities')
                    ->onUpdate('cascade')->onDelete('cascade');

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('measurements')) 
        {
            Schema::table('measurements', function(Blueprint $table)
            {
                $table->dropForeign(['physical_quantity_id']);
            });
            
            Schema::drop('measurements');
        }

    }
}
