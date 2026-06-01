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
        Schema::table('group_user', function (Blueprint $table) {
            $table->boolean('creator')->default(false);
            $table->boolean('admin')->default(false);
            $table->timestamp('invited')->nullable();
            $table->timestamp('accepted')->nullable();
            $table->timestamp('declined')->nullable();
            $table->string('token', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('group_user', function (Blueprint $table) {
            $table->dropColumn('creator');
            $table->dropColumn('admin');
            $table->dropColumn('invited');
            $table->dropColumn('accepted');
            $table->dropColumn('declined');
            $table->dropColumn('token');
        });
    }
};
