<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('organizations')) 
        {
            Schema::create('organizations', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->string('country_code')->nullable();
                $table->string('logo_url')->nullable();
                });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users','organization_id'))
        {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('organization_id')->unsigned()->nullable();
            });
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('organization_id')->references('id')->on('organizations')->onUpdate('cascade');
            });
        }
        if (Schema::hasTable('devices') && !Schema::hasColumn('devices','organization_id')) 
        {
            Schema::table('devices', function (Blueprint $table) {
                $table->integer('organization_id')->unsigned()->nullable();
            });
            Schema::table('devices', function (Blueprint $table) {
                $table->foreign('organization_id')->references('id')->on('organizations')->onUpdate('cascade');
            });
        }
        if (Schema::hasTable('sensors') && !Schema::hasColumn('sensors','organization_id')) 
        {
            Schema::table('sensors', function (Blueprint $table) {
                $table->integer('organization_id')->unsigned()->nullable();
            });
            Schema::table('sensors', function (Blueprint $table) {
                $table->foreign('organization_id')->references('id')->on('organizations')->onUpdate('cascade');
            });
        }
        if (Schema::hasTable('checklists') && !Schema::hasColumn('checklists','organization_id')) 
        {
            Schema::table('checklists', function (Blueprint $table) {
                $table->integer('organization_id')->unsigned()->nullable();
            });
            Schema::table('checklists', function (Blueprint $table) {
                $table->foreign('organization_id')->references('id')->on('organizations')->onUpdate('cascade');
            });
        }
        if (Schema::hasTable('locations') && !Schema::hasColumn('locations','organization_id')) 
        {
            Schema::table('locations', function (Blueprint $table) {
                $table->integer('organization_id')->unsigned()->nullable();
            });
            Schema::table('locations', function (Blueprint $table) {
                $table->foreign('organization_id')->references('id')->on('organizations')->onUpdate('cascade');
            });
        }
        if (Schema::hasTable('researches') && !Schema::hasColumn('researches','organization_id')) 
        {
           Schema::table('researches', function (Blueprint $table) {
                $table->integer('organization_id')->unsigned()->nullable();
            });
            Schema::table('researches', function (Blueprint $table) {
                $table->foreign('organization_id')->references('id')->on('organizations')->onUpdate('cascade');
            });
        }
        if (Schema::hasTable('location_researches') && !Schema::hasColumn('location_researches','organization_id')) 
        {
            Schema::table('location_researches', function (Blueprint $table) {
                $table->integer('organization_id')->unsigned()->nullable();
            });
            Schema::table('location_researches', function (Blueprint $table) {
                $table->foreign('organization_id')->references('id')->on('organizations')->onUpdate('cascade');
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
        if (Schema::hasTable('users') && Schema::hasColumn('users','organization_id')) 
        {
            Schema::table('users', function(Blueprint $table)
            {
                $table->dropForeign(['organization_id']);
            });
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('organization_id');
            });
        }
        if (Schema::hasTable('devices') && Schema::hasColumn('devices','organization_id')) 
        {
            Schema::table('devices', function(Blueprint $table)
            {
                $table->dropForeign(['organization_id']);
            });
            Schema::table('devices', function (Blueprint $table) {
                $table->dropColumn('organization_id');
            });
        }
         if (Schema::hasTable('sensors') && Schema::hasColumn('sensors','organization_id')) 
        {
            Schema::table('sensors', function(Blueprint $table)
            {
                $table->dropForeign(['organization_id']);
            });
            Schema::table('sensors', function (Blueprint $table) {
                $table->dropColumn('organization_id');
            });
        }
        if (Schema::hasTable('checklists') && Schema::hasColumn('checklists','organization_id')) 
        {
            Schema::table('checklists', function(Blueprint $table)
            {
                $table->dropForeign(['organization_id']);
            });
            Schema::table('checklists', function (Blueprint $table) {
                $table->dropColumn('organization_id');
            });
        }
        if (Schema::hasTable('locations') && Schema::hasColumn('locations','organization_id')) 
        {
            Schema::table('locations', function(Blueprint $table)
            {
                $table->dropForeign(['organization_id']);
            });
            Schema::table('locations', function (Blueprint $table) {
                $table->dropColumn('organization_id');
            });
        }
        if (Schema::hasTable('researches') && Schema::hasColumn('researches','organization_id')) 
        {
            Schema::table('researches', function(Blueprint $table)
            {
                $table->dropForeign(['organization_id']);
            });
            Schema::table('researches', function (Blueprint $table) {
                $table->dropColumn('organization_id');
            });
        }
        if (Schema::hasTable('location_researches') && Schema::hasColumn('location_researches','organization_id')) 
        {
            Schema::table('location_researches', function(Blueprint $table)
            {
                $table->dropForeign(['organization_id']);
            });
            Schema::table('location_researches', function (Blueprint $table) {
                $table->dropColumn('organization_id');
            });
        }

        Schema::drop('organizations');
    }
}
