<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlashlogColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('flash_logs', function (Blueprint $table) {
            $table->integer('log_size_bytes')->unsigned()->nullable();
            $table->boolean('log_erased')->nullable();
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
            $table->dropColumn('log_size_bytes');
            $table->dropColumn('log_erased');
        });
    }
}
