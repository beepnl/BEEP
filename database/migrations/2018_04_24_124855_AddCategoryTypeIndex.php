<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryTypeIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
    {
        if (Schema::hasTable('categories')) 
        {
            Schema::table('categories', function (Blueprint $table)
            {
                if (Schema::hasColumn('categories','type'))
                    $table->index('type');

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
        if (Schema::hasTable('categories')) 
        {
            Schema::table('categories', function (Blueprint $table)
            {
                if (Schema::hasColumn('categories','type'))
                    $table->dropIndex('categories_type_index');
            });
        }
    }
}
