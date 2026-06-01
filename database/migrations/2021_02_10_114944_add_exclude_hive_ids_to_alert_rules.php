<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('alert_rules')) {
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
    public function down(): void
    {
        if (Schema::hasTable('alert_rules')) {
            Schema::table('alert_rules', function (Blueprint $table) {
                $table->dropColumn('exclude_hive_ids');
            });
        }
    }
};
