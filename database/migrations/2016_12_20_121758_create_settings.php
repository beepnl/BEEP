<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::enableForeignKeyConstraints();

        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('category_id')->unsigned();
            //$table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade');
            $table->string('name')->nullable();
            $table->string('value')->nullable();
            $table->integer('number')->nullable();
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
        Schema::table('settings', function(Blueprint $table)
        {
            $table->dropForeign(['user_id']);
            //$table->dropForeignIfExists(['category_id']);
        });

        Schema::dropIfExists('settings');
    }
}
