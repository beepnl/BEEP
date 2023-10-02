<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceToMeasurement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->string('data_source_type')->default('db_influx');
            $table->string('data_api_url')->nullable();
            $table->string('data_repository_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->dropColumn('data_source_type');
            $table->dropColumn('data_api_url');
            $table->dropColumn('data_repository_url');
        });
    }
}
