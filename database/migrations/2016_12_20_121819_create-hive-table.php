<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hive_types', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->string('image')->nullable();
            $table->string('continents')->nullable();
            $table->string('info_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hives', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('location_id')->unsigned();
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('cascade');
            $table->integer('hive_type_id')->unsigned();
            $table->foreign('hive_type_id')->references('id')->on('hive_types')->onUpdate('cascade');
            $table->string('color')->nullable();
            $table->text('name')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
        });

        Schema::create('hive_layers', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->integer('hive_id')->unsigned();
            $table->foreign('hive_id')->references('id')->on('hives')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('category_id')->unsigned();
            $table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade');
            $table->tinyInteger('order');
            $table->string('color')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
        });

        Schema::create('hive_layer_frames', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->integer('layer_id')->unsigned();
            $table->foreign('layer_id')->references('id')->on('hive_layers')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('category_id')->unsigned();
            $table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade');
            $table->boolean('present')->default(true);
            $table->tinyInteger('order');
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hive_layer_frames', function(Blueprint $table)
        {
            $table->dropForeign(['layer_id']);
        });
        Schema::table('hive_layers', function(Blueprint $table)
        {
            $table->dropForeign(['hive_id']);
            $table->dropForeign(['category_id']);
        });
        Schema::table('hives', function(Blueprint $table)
        {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['location_id']);
            $table->dropForeign(['hive_type_id']);
        });

        Schema::dropIfExists('hive_layer_frames');
        Schema::dropIfExists('hive_layers');
        Schema::dropIfExists('hives');
        Schema::dropIfExists('hive_types');
    }
}
