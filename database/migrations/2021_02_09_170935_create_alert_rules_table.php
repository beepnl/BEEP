<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('alert_rules')) 
        {
            Schema::create('alert_rules', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->string('name')->nullable();
                $table->string('description')->nullable();
                $table->integer('measurement_id')->unsigned();
                $table->string('calculation');
                $table->integer('calculation_minutes')->unsigned();
                $table->string('comparator');
                $table->string('comparison');
                $table->float('threshold_value')->nullable();
                $table->string('exclude_months')->nullable();
                $table->string('exclude_hours')->nullable();
                $table->boolean('alert_via_email')->default(true);
                $table->string('webhook_url')->nullable();
                $table->boolean('active')->default(true);
                $table->integer('user_id')->unsigned()->nullable();
                $table->boolean('default_rule')->default(false);
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
            Schema::drop('alert_rules');
        }
    }
}
