<?php

use App\Queen;
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
        if (Schema::hasTable('queens') && ! Schema::hasColumn('queens', 'birth_date')) {
            Schema::table('queens', function (Blueprint $table) {
                $table->date('birth_date')->nullable();
            });

            $queens = Queen::all();
            foreach ($queens as $q) {
                $q->birth_date = date('Y-m-d', strtotime($q->created_at));
                $q->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('queens') && Schema::hasColumn('queens', 'birth_date')) {
            Schema::table('queens', function (Blueprint $table) {
                $table->dropColumn('birth_date');
            });
        }
    }
};
