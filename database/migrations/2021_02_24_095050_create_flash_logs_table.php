<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFlashLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('flash_logs')) 
        {
            Schema::create('flash_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->integer('user_id')->unsigned();
                $table->integer('device_id')->unsigned();
                $table->integer('hive_id')->unsigned()->nullable();
                $table->integer('log_messages')->unsigned()->nullable();
                $table->boolean('log_saved')->nullable();
                $table->boolean('log_parsed')->nullable();
                $table->boolean('log_has_timestamps')->nullable();
                $table->integer('bytes_received')->unsigned()->nullable();
                $table->string('log_file')->nullable();
                $table->string('log_file_stripped')->nullable();
                $table->string('log_file_parsed')->nullable();
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
        if (Schema::hasTable('flash_logs')) 
        {
            Schema::drop('flash_logs');
        }
    }
}
