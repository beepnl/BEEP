<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('file');
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->integer('height')->nullable();
            $table->integer('width')->nullable();
            $table->integer('size_kb')->nullable();
            $table->timestamp('date')->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('hive_id')->unsigned()->nullable();
            $table->integer('category_id')->unsigned()->nullable();
            $table->integer('checklist_id')->unsigned()->nullable();

            $table->foreign('user_id')->references('id')->on('users')
                    ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('hive_id')->references('id')->on('hives')
                    ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('category_id')->references('id')->on('categories')
                    ->onUpdate('cascade');

            $table->foreign('checklist_id')->references('id')->on('checklists')
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
        if (Schema::hasTable('images')) 
        {
            Schema::table('images', function (Blueprint $table) 
            {
                $table->dropForeign(['checklist_id']);
                $table->dropForeign(['category_id']);
                $table->dropForeign(['hive_id']);
                $table->dropForeign(['user_id']);
                $table->drop();
            });
        }
    }
}
