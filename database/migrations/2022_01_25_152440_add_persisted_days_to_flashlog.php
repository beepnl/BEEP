<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPersistedDaysToFlashlog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('flash_logs', function (Blueprint $table) {
            $table->float('persisted_days')->nullable();
            $table->integer('persisted_measurements')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('flash_logs', function (Blueprint $table) {
            $table->dropColumn('persisted_days');
            $table->dropColumn('persisted_measurements');
        });
    }
}
