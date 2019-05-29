<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGroupUserAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_user', function (Blueprint $table) {
            $table->boolean('creator')->default(false);
            $table->boolean('admin')->default(false);
            $table->timestamp('invited')->nullable();
            $table->timestamp('accepted')->nullable();
            $table->timestamp('declined')->nullable();
            $table->string('token',100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('group_user', function (Blueprint $table) {
            $table->dropColumn('creator');
            $table->dropColumn('admin');
            $table->dropColumn('invited');
            $table->dropColumn('accepted');
            $table->dropColumn('declined');
            $table->dropColumn('token');
        });
    }
}
