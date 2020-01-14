<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSensorDefinitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sensor_definitions', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name')->nullable();
            $table->boolean('inside')->nullable();
            $table->float('offset')->nullable();
            $table->float('multiplier')->nullable();
            $table->integer('input_measurement_id')->unsigned()->nullable();
            $table->integer('output_measurement_id')->unsigned()->nullable();
            $table->integer('device_id')->unsigned();
            
            $table->foreign('input_measurement_id')->references('id')->on('measurements')
                    ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('output_measurement_id')->references('id')->on('measurements')
                    ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('device_id')->references('id')->on('sensors')
                    ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('sensor_definitions')) 
        {
            Schema::table('sensor_definitions', function (Blueprint $table) 
            {
                $table->dropForeign(['device_id']);
                $table->dropForeign(['output_measurement_id']);
                $table->dropForeign(['input_measurement_id']);
                $table->drop();
            });
        }
    }
}
