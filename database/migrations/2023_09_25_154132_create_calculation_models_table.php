<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCalculationModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calculation_models', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name')->nullable();
            $table->integer('measurement_id')->unsigned();
            $table->integer('data_measurement_id')->unsigned();
            $table->string('data_interval')->default('1d');
            $table->integer('data_interval_amount')->default(1);
            $table->boolean('data_relative_interval')->default(true);
            $table->integer('data_interval_index')->default(0);
            $table->string('data_api_url')->nullable();
            $table->string('data_api_http_request')->nullable();
            $table->timestamp('data_last_call')->nullable();
            $table->string('calculation')->nullable();
            $table->string('repository_url')->nullable();
            $table->integer('calculation_interval_minutes')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('calculation_models');
    }
}
