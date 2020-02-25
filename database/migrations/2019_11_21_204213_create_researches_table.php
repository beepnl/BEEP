<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateResearchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('researches')) 
        {
            Schema::create('researches', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->string('url')->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->string('institution')->nullable();
            $table->string('type_of_data_used')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->softDeletes();
            });
        }

        if (!Schema::hasTable('research_user')) 
        {
            Schema::create('research_user', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->integer('user_id')->unsigned();
                $table->integer('research_id')->unsigned();
                $table->boolean('consent')->default(true);
                $table->string('consent_location_ids')->nullable();
                $table->string('consent_hive_ids')->nullable();
                $table->string('consent_sensor_ids')->nullable();

                $table->foreign('user_id')->references('id')->on('users')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('research_id')->references('id')->on('researches')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('checklist_research')) 
        {
            Schema::create('checklist_research', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->integer('research_id')->unsigned();
                $table->integer('checklist_id')->unsigned();

                $table->foreign('research_id')->references('id')->on('researches')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('checklist_id')->references('id')->on('checklists')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('research_user')) 
        {
            Schema::table('research_user', function (Blueprint $table) 
            {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['research_id']);
                $table->drop();
            });
        }

        if (Schema::hasTable('checklist_research')) 
        {
            Schema::table('checklist_research', function (Blueprint $table) 
            {
                $table->dropForeign(['checklist_id']);
                $table->dropForeign(['research_id']);
                $table->drop();
            });
        }

        if (Schema::hasTable('researches')) 
        {
            Schema::drop('researches');
        }
    }
}


