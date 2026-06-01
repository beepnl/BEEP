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
        Schema::table('flash_logs', function (Blueprint $table) {
            $table->datetime('log_date_start')->nullable();
            $table->datetime('log_date_end')->nullable();
            $table->float('logs_per_day')->nullable();
            $table->string('csv_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flash_logs', function (Blueprint $table) {
            $table->dropColumn('log_date_start');
            $table->dropColumn('log_date_end');
            $table->dropColumn('logs_per_day');
            $table->dropColumn('csv_url');
        });
    }
};
