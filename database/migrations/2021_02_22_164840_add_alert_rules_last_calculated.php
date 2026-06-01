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
    public function down(): void
    {
        if (Schema::hasTable('alert_rules')) {
            Schema::table('alert_rules', function (Blueprint $table) {
                $table->dropColumn('last_calculated_at');
                $table->dropColumn('timezone');
            });
        }
    }
};
