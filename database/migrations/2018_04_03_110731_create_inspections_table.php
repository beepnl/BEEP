<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInspectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('inspections')) 
        {
            Schema::create('inspections', function (Blueprint $table) 
            {
                $table->increments('id')->index();
                $table->text('notes', 300)->nullable();
                $table->text('reminder', 100)->nullable();
                $table->timestamp('reminder_date')->nullable();
                $table->integer('impression')->nullable();
                $table->boolean('attention')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->softDeletes();
            });
        }

        // Create table for associating inspections to hives (Many-to-Many)
        if (!Schema::hasTable('inspection_hive')) 
        {
            Schema::create('inspection_hive', function (Blueprint $table) {
                $table->integer('hive_id')->unsigned();
                $table->integer('inspection_id')->unsigned();

                $table->foreign('hive_id')->references('id')->on('hives')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('inspection_id')->references('id')->on('inspections')
                    ->onUpdate('cascade')->onDelete('cascade');

                $table->primary(['hive_id', 'inspection_id']);
            });
        }

        // Create table for associating inspections to location (Many-to-Many)
        if (!Schema::hasTable('inspection_location')) 
        {
            Schema::create('inspection_location', function (Blueprint $table) {
                $table->integer('location_id')->unsigned();
                $table->integer('inspection_id')->unsigned();

                $table->foreign('location_id')->references('id')->on('locations')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('inspection_id')->references('id')->on('inspections')
                    ->onUpdate('cascade')->onDelete('cascade');

                $table->primary(['location_id', 'inspection_id']);
            });
        }

        // Create table for associating inspections to users (Many-to-Many)
        if (!Schema::hasTable('inspection_user')) 
        {
            Schema::create('inspection_user', function (Blueprint $table) {
                $table->integer('user_id')->unsigned();
                $table->integer('inspection_id')->unsigned();

                $table->foreign('user_id')->references('id')->on('users')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('inspection_id')->references('id')->on('inspections')
                    ->onUpdate('cascade')->onDelete('cascade');

                $table->primary(['user_id', 'inspection_id']);
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
        if (Schema::hasTable('inspection_user')) 
        {
            Schema::table('inspection_user', function(Blueprint $table)
            {
                $table->dropForeign(['inspection_id']);
                $table->dropForeign(['user_id']);
            });
        }

        if (Schema::hasTable('inspection_location')) 
        {
            Schema::table('inspection_location', function(Blueprint $table)
            {
                $table->dropForeign(['inspection_id']);
                $table->dropForeign(['location_id']);
            });
        }

        if (Schema::hasTable('inspection_hive')) 
        {
            Schema::table('inspection_hive', function(Blueprint $table)
            {
                $table->dropForeign(['inspection_id']);
                $table->dropForeign(['hive_id']);
            });
        }

        Schema::dropIfExists('inspection_user');
        Schema::dropIfExists('inspection_location');
        Schema::dropIfExists('inspection_hive');
        Schema::dropIfExists('inspections');
    }
}
