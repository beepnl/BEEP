<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sensor_definitions', function ($table) {
            $table->dropForeign(['device_id']);
            $table->foreign('device_id')->references('id')->on('sensors')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensor_definitions', function ($table) {
            $table->dropForeign(['device_id']);
            $table->foreign('device_id')->references('id')->on('sensors')->onUpdate('cascade');
            $table->dropColumn('deleted_at');
        });
    }
};
