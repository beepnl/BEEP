<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFutureToAlertRuleFormulas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('alert_rule_formulas') && Schema::hasColumn('alert_rule_formulas','future') == false) 
        {

            Schema::table('alert_rule_formulas', function (Blueprint $table) {
                $table->boolean('future')->default(false);
            });

            // Transfer current AlertRule settings to formula's
            $alert_rules = \App\Models\AlertRule::all();
            $ar_count    = $alert_rules->count();
            $ars_mod     = 0;

            foreach ($alert_rules as $ar_id => $ar)
            {
                $arf                    = new App\Models\AlertRuleFormula();
                $arf->alert_rule_id     = $ar->id;
                $arf->measurement_id    = $ar->measurement_id;
                $arf->calculation       = $ar->calculation;
                $arf->comparator        = $ar->comparator;
                $arf->comparison        = $ar->comparison;
                $arf->logical           = null;
                $arf->period_minutes    = $ar->calculation_minutes;
                $arf->threshold_value   = $ar->threshold_value;
                $arf->future            = false;
                $arf->save();
            }

            // Remove formula fields from the AlertRule table 
            // Schema::table('alert_rules', function (Blueprint $table) {
            //     $table->dropColumn('measurement_id');
            //     $table->dropColumn('calculation');
            //     $table->dropColumn('comparator');
            //     $table->dropColumn('comparison');
            //     $table->dropColumn('threshold_value');
            // });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alert_rule_formulas', function (Blueprint $table) {
            $table->dropColumn('future');
        });
    }
}
