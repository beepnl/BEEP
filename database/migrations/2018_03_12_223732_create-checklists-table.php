<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChecklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
        public function up()
    {
        if (!Schema::hasTable('checklists')) 
        {
            Schema::create('checklists', function (Blueprint $table) {
                $table->increments('id')->index();
                $table->string('type')->nullable();
                $table->string('name')->nullable();
                $table->string('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Create table for associating checklists to category items (Many-to-Many)
        if (!Schema::hasTable('checklist_category')) 
        {
            Schema::create('checklist_category', function (Blueprint $table) {
                $table->integer('category_id')->unsigned();
                $table->integer('checklist_id')->unsigned();

                $table->foreign('category_id')->references('id')->on('categories')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('checklist_id')->references('id')->on('checklists')
                    ->onUpdate('cascade')->onDelete('cascade');

                $table->primary(['category_id', 'checklist_id']);
            });
        }

        // Create table for associating checklists to hives (Many-to-Many)
        if (!Schema::hasTable('checklist_hive')) 
        {
            Schema::create('checklist_hive', function (Blueprint $table) {
                $table->integer('hive_id')->unsigned();
                $table->integer('checklist_id')->unsigned();

                $table->foreign('hive_id')->references('id')->on('hives')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('checklist_id')->references('id')->on('checklists')
                    ->onUpdate('cascade')->onDelete('cascade');

                $table->primary(['hive_id', 'checklist_id']);
            });
        }

        // Create table for associating checklists to users (Many-to-Many)
        if (!Schema::hasTable('checklist_user')) 
        {
            Schema::create('checklist_user', function (Blueprint $table) {
                $table->integer('user_id')->unsigned();
                $table->integer('checklist_id')->unsigned();

                $table->foreign('user_id')->references('id')->on('users')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('checklist_id')->references('id')->on('checklists')
                    ->onUpdate('cascade')->onDelete('cascade');

                $table->primary(['user_id', 'checklist_id']);
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
        if (Schema::hasTable('checklist_user')) 
        {
            Schema::table('checklist_user', function(Blueprint $table)
            {
                $table->dropForeign(['checklist_id','user_id']);
            });
        }
        if (Schema::hasTable('checklist_hive')) 
        {
            Schema::table('checklist_hive', function(Blueprint $table)
            {
                $table->dropForeign(['checklist_id','hive_id']);
            });
        }
        if (Schema::hasTable('checklist_category')) 
        {
            Schema::table('checklist_category', function(Blueprint $table)
            {
                $table->dropForeign(['checklist_id','category_id']);
            });
        }

        Schema::dropIfExists('checklist_user');
        Schema::dropIfExists('checklist_hive');
        Schema::dropIfExists('checklist_category');
        Schema::dropIfExists('checklists');
    }
}
