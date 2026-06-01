<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('alert_rule_formulas')) {
            Schema::table('alert_rule_formulas', function (Blueprint $table) {
                $table->float('last_result_value')->nullable();
                $table->boolean('last_evaluation_value')->default(false);
                $table->timestamp('last_evaluated_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alert_rule_formulas', function (Blueprint $table) {
            $table->dropColumn('last_result_value');
            $table->dropColumn('last_evaluation_value');
            $table->dropColumn('last_evaluated_at');
        });
    }
};
