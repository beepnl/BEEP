<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationRoofed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) 
        {
            if (!Schema::hasColumn('locations','roofed'))
                $table->boolean('roofed')->nullable();  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locations', function (Blueprint $table) 
        {
            if (Schema::hasColumn('locations','roofed'))
                $table->dropColumn('roofed');  
        });
    }
}
