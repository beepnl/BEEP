<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupHive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('group_hive')) 
        {
            Schema::create('group_hive', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('hive_id')->unsigned();
                $table->integer('group_id')->unsigned();
                $table->boolean('edit_hive')->default(false);
                $table->boolean('edit_sensors')->default(false);
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('deleted_at')->nullable();

                $table->foreign('hive_id')->references('id')->on('hives')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('group_id')->references('id')->on('groups')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('group_hive')) 
        {
            Schema::table('group_hive', function(Blueprint $table)
            {
                $table->dropForeign(['hive_id']);
                $table->dropForeign(['group_id']);
            });
            Schema::dropIfExists('group_hive');
        }
    }
}
