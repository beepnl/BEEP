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
        if (Schema::hasTable('checklist_category')) {
            Schema::table('checklist_category', function (Blueprint $table) {
                if (Schema::hasColumn('checklist_category', 'order') == false) {
                    $table->integer('order')->unsigned()->nullable();
                }

            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasTable('checklist_category')) {
            Schema::table('checklist_category', function (Blueprint $table) {
                $table->dropColumn('order');
            });
        }
    }
};
