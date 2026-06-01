<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('sensors', function (Blueprint $table) {
            DB::statement('ALTER TABLE `sensors` MODIFY COLUMN `datetime_offset_sec` DOUBLE NULL;');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->float('datetime_offset_sec')->change();
        });
    }
};
