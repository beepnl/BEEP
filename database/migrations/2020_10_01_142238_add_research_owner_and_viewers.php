<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('researches')) {
            Schema::table('researches', function (Blueprint $table) {
                $table->integer('user_id')->unsigned()->nullable();
            });
        }

        if (! Schema::hasTable('research_viewer')) {
            Schema::create('research_viewer', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->integer('user_id')->unsigned();
                $table->integer('research_id')->unsigned();

                $table->foreign('user_id')->references('id')->on('users')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('research_id')->references('id')->on('researches')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('research_viewer')) {
            Schema::table('research_viewer', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['research_id']);
                $table->drop();
            });
        }

        if (Schema::hasTable('researches')) {
            Schema::table('researches', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
};
