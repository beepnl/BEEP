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
    public function up(): void
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
    public function down(): void
    {
        Schema::table('hives', function ($table) {
            $table->dropForeign(['location_id']);
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('cascade');
        });
    }
};
