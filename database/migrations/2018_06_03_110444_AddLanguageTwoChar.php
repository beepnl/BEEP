<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Language;

class AddLanguageTwoChar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('languages', function ($table) 
        {
            if (!Schema::hasColumn('languages','twochar'))
                $table->string('twochar', 2)->nullable();
        });

        $languages = Language::all();
        foreach ($languages as $l) 
        {
            if ($l->twochar == null)
            {
                $l->twochar = substr($l->abbreviation, 0, 2);
                $l->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('languages', function ($table) {
            if (Schema::hasColumn('languages','twochar'))
                $table->dropColumn('twochar');
        });
    }
}
