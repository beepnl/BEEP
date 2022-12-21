<?php

namespace App\Jobs;

use App\Http\Controllers\FlashLogController;
use App\Http\Controllers\Api\FlashLogController;
use App\Http\Requests\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class FlashLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    // use withoutRelations?

    public $FlashLogController;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->FlashLogController = $FlashLogController;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FlashLogController $flashLogController)
    {

    // parse flashlog via Controller/Api/FlashlogController
        FlashLogController::matchPercentage($array1, $array2, $match_props=9, $max_diff_percentage=0);
        FlashLogController::parse(Request $request, $id, $persist=false, $delete=false);
        FlashLogController::storeInfluxDataArrays($data_arrays, $device);
        
        return void;    
    }

    /**
     * Dispatch the job to queue.
     *
     * @return void
     */
    public function queueFlashLog(Request $request)
    {
        $queuedFlashLog = store(Request $request);

        FlashLogJob::dispatch($queuedFlashLog);    
        $flash
    }

    /*

    queueFlashLog(function () use ($queuedFlashLog) {
        $queuedFlashLog->handle();
    })->catch(function (Throwable $e)
    { 
        // failed job
    });

    */
    
}