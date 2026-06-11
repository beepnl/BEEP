<?php

use App\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('languages', function ($table) {
            if (! Schema::hasColumn('languages', 'twochar')) {
                $table->string('twochar', 2)->nullable();
            }
        });

        $languages = Language::all();
        foreach ($languages as $l) {
            if ($l->twochar == null) {
                $l->twochar = substr($l->abbreviation, 0, 2);
                $l->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('languages', function ($table) {
            if (Schema::hasColumn('languages', 'twochar')) {
                $table->dropColumn('twochar');
            }
        });
    }
};
