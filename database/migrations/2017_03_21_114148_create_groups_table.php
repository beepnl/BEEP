<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
        });

        // Create table for associating groups to sensors (Many-to-Many)
        Schema::create('group_sensor', function (Blueprint $table) {
            $table->integer('sensor_id')->unsigned();
            $table->integer('group_id')->unsigned();

            $table->foreign('sensor_id')->references('id')->on('sensors')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['sensor_id', 'group_id']);
        });

        // Create table for associating groups to users (Many-to-Many)
        Schema::create('group_user', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('group_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['user_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_sensor');
        Schema::dropIfExists('group_user');
        Schema::dropIfExists('groups');
    }
}
