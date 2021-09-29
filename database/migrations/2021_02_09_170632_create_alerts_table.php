<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('alerts')) 
        {
            Schema::create('alerts', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->integer('alert_rule_id')->unsigned();
                $table->string('alert_function')->nullable();
                $table->string('alert_value')->nullable();
                $table->integer('measurement_id')->unsigned();
                $table->boolean('show')->default(true);
                $table->string('location_name')->nullable();
                $table->string('hive_name')->nullable();
                $table->string('device_name')->nullable();
                $table->integer('location_id')->unsigned()->nullable();
                $table->integer('hive_id')->unsigned()->nullable();
                $table->integer('device_id')->unsigned()->nullable();
                $table->integer('user_id')->unsigned()->nullable();
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
        if (Schema::hasTable('alerts')) 
        {
            Schema::drop('alerts');
        }
    }
}
