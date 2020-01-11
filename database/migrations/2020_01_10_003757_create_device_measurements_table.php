<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDeviceMeasurementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_measurements', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name')->nullable();
            $table->boolean('inside')->nullable();
            $table->float('zero_value')->nullable();
            $table->float('unit_per_value')->nullable();
            $table->integer('measurement_id')->unsigned()->nullable();
            $table->integer('physical_quantity_id')->unsigned()->nullable();
            $table->integer('sensor_id')->unsigned();
            
            $table->foreign('measurement_id')->references('id')->on('measurements')
                    ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('physical_quantity_id')->references('id')->on('physical_quantities')
                    ->onUpdate('cascade');

            $table->foreign('sensor_id')->references('id')->on('sensors')
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
        if (Schema::hasTable('device_measurements')) 
        {
            Schema::table('device_measurements', function (Blueprint $table) 
            {
                $table->dropForeign(['sensor_id']);
                $table->dropForeign(['physical_quantity_id']);
                $table->dropForeign(['measurement_id']);
                $table->drop();
            });
        }
    }
}
