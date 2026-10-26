<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // This migration was previously named 2026_06_02_000001_add_expiration_index_to_cache_table (ran before the cache table was created on fresh installs).
    // Databases that already ran it under that name have the indexes, so only add/drop them when needed.
    public function up()
    {
        foreach (['cache', 'cache_locks'] as $tableName) {
            if (! Schema::hasIndex($tableName, ['expiration'])) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->index('expiration');
                });
            }
        }
    }

    public function down()
    {
        foreach (['cache', 'cache_locks'] as $tableName) {
            if (Schema::hasIndex($tableName, ['expiration'])) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropIndex(['expiration']);
                });
            }
        }
    }
};
