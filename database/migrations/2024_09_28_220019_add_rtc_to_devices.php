<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Device;

class AddRtcToDevices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->boolean('rtc')->nullable();
        });

        foreach (Device::all() as $d)
        {
            if ($d->last_downlink_result == 'RTC installed')
            {
                $d->last_downlink_result = '';
                $d->rtc = true;
                $d->save();
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
        Schema::table('sensors', function (Blueprint $table) {
            $table->dropColumn('rtc');
        });
    }
}
