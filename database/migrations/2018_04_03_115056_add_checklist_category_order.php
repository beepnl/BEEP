<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChecklistCategoryOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('checklist_category')) 
        {
            Schema::table('checklist_category', function (Blueprint $table)
            {
                if (Schema::hasColumn('checklist_category','order') == false)
                    $table->integer('order')->unsigned()->nullable();

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
        if (Schema::hasTable('checklist_category')) 
        {
            Schema::table('checklist_category', function (Blueprint $table)
            {
                $table->dropColumn('order');
            });
        }
    }
}
