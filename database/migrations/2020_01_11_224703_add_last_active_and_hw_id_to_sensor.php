<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastActiveAndHwIdToSensor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->timestamp('last_message_received')->nullable();
            $table->string('hardware_id')->nullable();
            $table->string('firmware_version')->nullable();
            $table->string('hardware_version')->nullable();
            $table->integer('boot_count')->nullable();
            $table->float('measurement_interval_min')->nullable();
            $table->float('measurement_transmission_ratio')->nullable();
            $table->string('ble_pin')->nullable();
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
            $table->dropColumn('last_message_received');
            $table->dropColumn('hardware_id');
            $table->dropColumn('firmware_version');
            $table->dropColumn('hardware_version');
            $table->dropColumn('boot_count');
            $table->dropColumn('measurement_interval_min');
            $table->dropColumn('measurement_transmission_ratio');
            $table->dropColumn('ble_pin');
        });
    }
}
