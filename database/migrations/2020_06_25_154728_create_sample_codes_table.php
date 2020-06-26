<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSampleCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sample_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('sample_code')->unique();
            $table->text('sample_note')->nullable();
            $table->timestamp('sample_date')->nullable();
            $table->text('test_result')->nullable();
            $table->text('test')->nullable();
            $table->timestamp('test_date')->nullable();
            $table->text('test_lab_name')->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('hive_id')->unsigned()->nullable();
            $table->integer('queen_id')->unsigned()->nullable();

            $table->foreign('user_id')->references('id')->on('users')
                    ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('hive_id')->references('id')->on('hives')
                    ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('queen_id')->references('id')->on('queens')
                    ->onUpdate('cascade');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('sample_codes')) 
        {
            Schema::table('sample_codes', function (Blueprint $table) 
            {
                $table->dropForeign(['queen_id']);
                $table->dropForeign(['hive_id']);
                $table->dropForeign(['user_id']);
                $table->drop();
            });
        }
    }
}
