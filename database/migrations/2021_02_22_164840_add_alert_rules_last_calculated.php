<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAlertRulesLastCalculated extends Migration
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
            Schema::table('alert_rules', function (Blueprint $table) 
            {
                $table->timestamp('last_calculated_at')->nullable();
                $table->string('timezone')->default('Europe/Amsterdam');
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
            Schema::table('alert_rules', function (Blueprint $table) 
            {
                $table->dropColumn('last_calculated_at');
                $table->dropColumn('timezone');
            });
        }
    }
}
