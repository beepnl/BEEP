<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddDeviceSensorDefinitionDeleteForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sensor_definitions', function ($table) {
            $table->dropForeign(['device_id']);
            $table->foreign('device_id')->references('id')->on('sensors')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sensor_definitions', function ($table) {
            $table->dropForeign(['device_id']);
            $table->foreign('device_id')->references('id')->on('sensors')->onUpdate('cascade');
            $table->dropColumn('deleted_at');
        });
    }
}
