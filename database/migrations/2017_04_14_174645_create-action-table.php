<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActionTable extends Migration
{
   /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->integer('hive_id')->unsigned();
            $table->foreign('hive_id')->references('id')->on('hives')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('category_id')->unsigned();
            $table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade');
            $table->text('text')->nullable();
            $table->float('number')->nullable();
            $table->tinyInteger('score')->nullable();
            $table->boolean('boolean')->nullable();
            $table->timestamp('date')->nullable();
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
        Schema::table('actions', function(Blueprint $table)
        {
            $table->dropForeign(['hive_id']);
            $table->dropForeign(['category_id']);
        });

        Schema::dropIfExists('actions');
    }
}
