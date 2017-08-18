<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bee_races', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('synonyms')->nullable();
            $table->string('continents')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
           
        Schema::create('queens', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->integer('hive_id')->unsigned();
            $table->foreign('hive_id')->references('id')->on('hives')->onUpdate('cascade');
            $table->integer('race_id')->unsigned();
            $table->foreign('race_id')->references('id')->on('bee_races')->onUpdate('cascade');
            $table->string('name')->nullable();
            $table->tinyInteger('quality')->nullable(); 
            $table->boolean('fertilized')->defalut(false);
            $table->boolean('clipped')->defalut(false);
            $table->string('fertilizing_location')->nullable();
            $table->string('origin')->nullable();
            $table->string('tree')->nullable();
            $table->string('line')->nullable();
            $table->integer('mother_id')->nullable();
            $table->string('marker')->nullable();
            $table->string('color')->nullable();
            $table->string('goal')->nullable();
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
        Schema::table('queens', function(Blueprint $table){
            $table->dropForeign(['hive_id']);
            $table->dropForeign(['race_id']);
        });

        Schema::dropIfExists('queens');
        Schema::dropIfExists('bee_races');
    }
}
