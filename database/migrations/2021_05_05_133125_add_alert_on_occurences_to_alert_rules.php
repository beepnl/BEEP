<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAlertOnOccurencesToAlertRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alert_rules', function (Blueprint $table) {
            $table->integer('alert_on_occurences')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alert_rules', function (Blueprint $table) {
            $table->dropColumn('alert_on_occurences');
        });
    }
}
