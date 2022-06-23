<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInspectionChecklistId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inspections', function (Blueprint $table) 
        {
            if (!Schema::hasColumn('inspections','checklist_id'))
            {
                $table->integer('checklist_id')->unsigned()->nullable();
                $table->foreign('checklist_id')->references('id')->on('checklists')
                    ->onUpdate('cascade');
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
        Schema::table('inspections', function (Blueprint $table) 
        {
            if (Schema::hasColumn('inspections','checklist_id'))
            {
                $table->dropForeign(['checklist_id']);
                $table->dropColumn('checklist_id');  
            }
        });
    }
}
