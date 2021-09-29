<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExcludeHiveIdsToAlertRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('alert_rules')) 
        {
            Schema::table('alert_rules', function (Blueprint $table) {
                $table->string('exclude_hive_ids')->nullable();
                $table->integer('calculation_minutes')->unsigned()->nullable()->change();
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
        if (Schema::hasTable('alert_rules')) 
        {
            Schema::table('alert_rules', function (Blueprint $table) {
                $table->dropColumn('exclude_hive_ids');
            });
        }
    }
}
