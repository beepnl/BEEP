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
        Schema::table('flash_logs', function (Blueprint $table) {
            $table->integer('log_size_bytes')->unsigned()->nullable();
            $table->boolean('log_erased')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flash_logs', function (Blueprint $table) {
            $table->dropColumn('log_size_bytes');
            $table->dropColumn('log_erased');
        });
    }
};
