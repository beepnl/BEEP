<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('inspection_items')) {
            Schema::create('inspection_items', function (Blueprint $table) {
                $table->increments('id');
                $table->string('value')->nullable();
                $table->integer('inspection_id')->unsigned();
                $table->foreign('inspection_id')->references('id')->on('inspections')->onUpdate('cascade')->onDelete('cascade');
                $table->integer('category_id')->unsigned();
                $table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade')->onDelete('cascade');
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inspection_items')) {
            Schema::table('inspection_items', function (Blueprint $table) {
                $table->dropForeign(['inspection_id']);
                $table->dropForeign(['category_id']);
            });
        }

        Schema::dropIfExists('inspection_items');
    }
};
