<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('continents', function (Blueprint $table) 
        {
            $table->increments('id')->index();
            $table->text('name')->nullable();
            $table->char('abbr', 2)->nullable();
        });

        Schema::create('locations', function (Blueprint $table) 
        {
            $table->increments('id')->index();
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('continent_id')->unsigned();
            $table->foreign('continent_id')->references('id')->on('continents')->onUpdate('cascade');
            $table->integer('category_id')->unsigned();
            $table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade');
            $table->text('name');
            $table->float('coordinate_lat', 7, 4)->nullable(); // Signed degrees format (DDD.dddd) -> location to within 1 millimeter
            $table->float('coordinate_lon', 7, 4)->nullable();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('street_no')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country_code')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locations', function(Blueprint $table)
        {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['continent_id']);
            $table->dropForeign(['category_id']);
        });

        Schema::dropIfExists('locations');
        Schema::dropIfExists('continents');
    }
}
