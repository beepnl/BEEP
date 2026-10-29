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
        Schema::table('inspections', function (Blueprint $table) {
            if (! Schema::hasColumn('inspections', 'checklist_id')) {
                $table->integer('checklist_id')->unsigned()->nullable();
                $table->foreign('checklist_id')->references('id')->on('checklists')
                    ->onUpdate('cascade');
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            if (Schema::hasColumn('inspections', 'checklist_id')) {
                $table->dropForeign(['checklist_id']);
                $table->dropColumn('checklist_id');
            }
        });
    }
};
