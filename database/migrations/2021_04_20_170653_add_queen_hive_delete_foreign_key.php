<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('queens', function ($table) {
            $table->dropForeign(['hive_id']);
            $table->foreign('hive_id')->references('id')->on('hives')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('queens', function ($table) {
            $table->dropForeign(['hive_id']);
            $table->foreign('hive_id')->references('id')->on('hives')->onUpdate('cascade');
        });
    }
};
