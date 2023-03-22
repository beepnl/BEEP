<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDashboardGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashboard_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('user_id')->unsigned();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->json('hive_ids')->nullable();
            $table->integer('speed')->unsigned();
            $table->string('interval');
            $table->boolean('show_inspections')->default(false);
            $table->boolean('show_all')->default(false);
            $table->boolean('hide_measurements')->default(false);
            $table->string('logo_url')->nullable();

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dashboard_groups', function(Blueprint $table)
            {
                $table->dropForeign(['user_id']);
            });
        Schema::dropIfExists('dashboard_groups');
    }
}
