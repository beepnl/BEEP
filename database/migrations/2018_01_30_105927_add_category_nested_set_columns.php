<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Kalnoy\Nestedset\NestedSet;

class AddCategoryNestedSetColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) 
        {
            if (Schema::hasColumn('categories',NestedSet::LFT) == false)
            {
                $table->unsignedInteger(NestedSet::LFT)->default(0);
                $table->unsignedInteger(NestedSet::RGT)->default(0);
                $table->index(NestedSet::getDefaultColumns()); // assuming parent_id is already there
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
            $table->dropForeign(['parent_id']);
            NestedSet::dropColumns($table);
        });
    }
}
