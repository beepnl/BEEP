<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertRuleFormulasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('alert_rule_formulas')) 
        {

            Schema::create('alert_rule_formulas', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->integer('alert_rule_id')->unsigned();
                $table->integer('measurement_id')->unsigned();
                $table->string('calculation')->nullable();
                $table->string('comparator')->nullable();
                $table->string('comparison')->nullable();
                $table->string('logical')->nullable();
                $table->integer('period_minutes')->nullable();
                $table->float('threshold_value')->nullable();
            });

            // Transfer current AlertRule settings to formula's
            // $alert_rules = \App\Models\AlertRule::all();
            // $ar_count    = $alert_rules->count();
            // $ars_mod     = 0;

            // foreach ($alert_rules as $ar_id => $ar)
            // {
            //     $arf                    = new App\Models\AlertRuleFormula();
            //     $arf->alert_rule_id     = $ar->id;
            //     $arf->measurement_id    = $ar->measurement_id;
            //     $arf->calculation       = $ar->calculation;
            //     $arf->comparator        = $ar->comparator;
            //     $arf->comparison        = $ar->comparison;
            //     $arf->logical           = null;
            //     $arf->period_minutes    = $ar->calculation_minutes;
            //     $arf->threshold_value   = $ar->threshold_value;
            //     $arf->save();
            // }

            // Remove formula fields from the AlertRule table 

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alert_rule_formulas');
    }
}
