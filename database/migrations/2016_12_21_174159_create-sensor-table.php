<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSensorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sensors', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('hive_id')->unsigned();
            $table->foreign('hive_id')->references('id')->on('hives')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('category_id')->unsigned();
            $table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade');
            $table->text('name')->nullable();
            $table->string('key')->unique();
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
        });

        // Create table for associating sensors to users (Many-to-Many)
        Schema::create('sensor_user', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('sensor_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('sensor_id')->references('id')->on('sensors')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['user_id', 'sensor_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sensors', function(Blueprint $table)
        {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['hive_id']);
            $table->dropForeign(['category_id']);
        });
        
        Schema::dropIfExists('sensor_user');
        Schema::dropIfExists('sensors');
    }
}
