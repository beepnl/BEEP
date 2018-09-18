<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationHiveDeleteForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hives', function ($table) {
            $table->dropForeign(['location_id']);
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hives', function ($table) {
            $table->dropForeign(['location_id']);
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('cascade');
        });
    }
}
