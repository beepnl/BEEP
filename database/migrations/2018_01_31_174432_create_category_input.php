<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryInput extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('category_inputs')) 
        {
            Schema::create('category_inputs', function (Blueprint $table) 
            {
                $table->increments('id')->index();
                $table->string('name')->index();
                $table->string('type');
                $table->string('min')->nullable();
                $table->string('max')->nullable();
                $table->integer('decimals')->nullable();
                $table->string('icon')->nullable();
            });
        }

        if (!Schema::hasTable('physical_quantities')) 
        {
            Schema::create('physical_quantities', function (Blueprint $table) 
            {
                $table->increments('id')->index();
                $table->string('name')->index();
                $table->string('unit');
                $table->string('abbreviation')->nullable();
            });
        }

        if (!Schema::hasTable('languages')) 
        {
            Schema::create('languages', function (Blueprint $table) 
            {
                $table->increments('id')->index();
                $table->string('abbreviation')->index();
                $table->string('name')->index();
                $table->string('name_english')->nullable();
                $table->string('icon')->nullable();
                $table->string('twochar', 2)->nullable();
            });
        }

        if (!Schema::hasTable('translations')) 
        {
            Schema::create('translations', function (Blueprint $table) 
            {
                $table->increments('id')->index();
                $table->string('name')->index();
                $table->string('type')->nullable();
                $table->integer('language_id')->unsigned();
                $table->foreign('language_id')->references('id')->on('languages')->onUpdate('cascade');
                $table->text('translation');
            });
        }

        Schema::table('categories', function (Blueprint $table) 
        {
            if (Schema::hasColumn('categories','icon') == false)
            {
                $table->string('icon')->nullable();
                $table->text('source')->nullable();
                $table->text('description')->nullable();

                $table->integer('category_input_id')->unsigned()->nullable();
                $table->foreign('category_input_id')->references('id')->on('category_inputs')->onUpdate('cascade');
                $table->integer('physical_quantity_id')->unsigned()->nullable();
                $table->foreign('physical_quantity_id')->references('id')->on('physical_quantities')->onUpdate('cascade');


            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) 
        {

            $table->dropForeign(['category_input_id']);
            $table->dropForeign(['physical_quantity_id']);

            $table->dropColumn('physical_quantity_id');
            $table->dropColumn('category_input_id');
            $table->dropColumn('description');
            $table->dropColumn('source');
            $table->dropColumn('icon');
        });

        Schema::table('translations', function(Blueprint $table)
        {
            $table->dropForeign(['language_id']);
        });

        Schema::dropIfExists('translations');
        Schema::dropIfExists('physical_quantities');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('category_inputs');
    }
}
