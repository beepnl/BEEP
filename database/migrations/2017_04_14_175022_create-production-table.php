<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->integer('hive_id')->unsigned();
            $table->foreign('hive_id')->references('id')->on('hives')->onUpdate('cascade');
            $table->integer('frame_id')->unsigned();
            $table->foreign('frame_id')->references('id')->on('hive_layer_frames')->onUpdate('cascade');
            $table->integer('category_id')->unsigned();
            $table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade');
            $table->string('name')->nullable();
            $table->string('queen_cell_stage')->nullable();
            $table->string('queen_cell_type')->nullable();
            $table->tinyInteger('perc_brood')->nullable();
            $table->tinyInteger('perc_honey')->nullable();
            $table->tinyInteger('perc_pollen')->nullable();
            $table->tinyInteger('perc_wax')->nullable();
            $table->float('weight_kg')->nullable();
            $table->tinyInteger('brood_perc_all_stages')->nullable();
            $table->tinyInteger('brood_perc_open')->nullable();
            $table->tinyInteger('brood_perc_queen')->nullable();
            $table->tinyInteger('brood_perc_drone')->nullable();
            $table->tinyInteger('brood_perc_worker')->nullable();
            $table->tinyInteger('pattern_score')->nullable(); 
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
        Schema::table('productions', function(Blueprint $table)
        {
            $table->dropForeign(['hive_id']);
            $table->dropForeign(['frame_id']);
            $table->dropForeign(['category_id']);
        });

        Schema::dropIfExists('productions');
    }
}
