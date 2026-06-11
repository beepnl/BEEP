<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->string('image_url', 1024)->change();
            $table->string('thumb_url', 1024)->change();
        });

        Schema::table('researches', function (Blueprint $table) {
            $table->dropColumn('image');
            $table->integer('image_id')->unsigned()->nullable();
        });

        Schema::table('inspection_items', function (Blueprint $table) {
            $table->string('value', 1024)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->string('image_url', 255)->change();
            $table->string('thumb_url', 255)->change();
        });
        Schema::table('researches', function (Blueprint $table) {
            $table->dropColumn('image_id');
            $table->string('image', 255)->nullable();
        });
        Schema::table('inspection_items', function (Blueprint $table) {
            $table->string('value', 255)->change();
        });
    }
};
