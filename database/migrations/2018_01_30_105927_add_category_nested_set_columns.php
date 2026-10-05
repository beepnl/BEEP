<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NestedSet;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', NestedSet::LFT) == false) {
                $table->unsignedInteger(NestedSet::LFT)->default(0);
                $table->unsignedInteger(NestedSet::RGT)->default(0);
                $table->index(NestedSet::getDefaultColumns()); // assuming parent_id is already there
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            NestedSet::dropColumns($table);
        });
    }
};
