<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Queen;

class AddQueenBirthDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('queens') && !Schema::hasColumn('queens','birth_date')) 
        {
            Schema::table('queens', function (Blueprint $table) {
                $table->date('birth_date')->nullable();
            });


            $queens = Queen::all();
            foreach ($queens as $q)
            {
                $q->birth_date = date('Y-m-d', strtotime($q->created_at));
                $q->save();
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
        if (Schema::hasTable('queens') && Schema::hasColumn('queens','birth_date'))
        {
            Schema::table('queens', function (Blueprint $table) {
                $table->dropColumn('birth_date');
            });
        }
    }
}
