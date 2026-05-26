<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlertRulesLastEvaluated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('alert_rules')) {
            Schema::table('alert_rules', function (Blueprint $table) {
                $table->timestamp('last_evaluated_at')->nullable();
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
        if (Schema::hasTable('alert_rules')) {
            Schema::table('alert_rules', function (Blueprint $table) {
                $table->dropColumn('last_evaluated_at');
            });
        }
    }
}
