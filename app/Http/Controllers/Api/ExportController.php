<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Excel;
use Response;
use Storage;
use Mail;
use App\Setting;
use App\Hive;
use App\User;
use App\Category;
use App\Inspection;
use App\InspectionItem;
use App\Measurement;
use App\Mail\DataExport;
use App\Exports\HiveExport;
use Moment\Moment;

/**
 * @group Api\ExportController
 * Export all data to an Excel file by email (GDPR)
 */
class ExportController extends Controller
{
    public function all(Request $request)
    {
        $fileType = $request->filled('fileFormat') ? $request->input('fileFormat') : 'xlsx';
        $fileName = strtolower(env('APP_NAME')).'-export-'.$request->user()->id.time();
        $user     = $request->user();

        $item_names = Inspection::item_names($user->inspections()->get());
        $userExport = $this->getUser($user);
        $hiveExport = $this->getHives($user);
        $locaExport = $this->getLocations($user);
        $inspExport = $this->getInspections($user, $item_names);


        $file = Excel::create($fileName, function($excel) use ($request, $fileName, $userExport, $hiveExport, $locaExport, $inspExport) 
        {

            // Set the title
            $excel->setTitle($fileName);

            // Chain the setters
            $excel->setCreator(env('APP_NAME'))
                  ->setCompany(env('APP_NAME'));

            // Call them separately
            $excel->setDescription($fileName);

            //Make sheets
            $excel->sheet(__('export.user'), function($sheet) use ($userExport) 
            {
                $sheet->freezeFirstRow();
                $sheet->fromModel($userExport);
            });
            // Bug https://github.com/Maatwebsite/Laravel-Excel/issues/2478
            $excel->sheet(__('export.locations'), function($sheet) use ($locaExport) 
            {
                $sheet->freezeFirstRow();
                $sheet->fromModel($locaExport);
            });
            $excel->sheet(__('export.hives'), function($sheet) use ($hiveExport) 
            {
                $sheet->freezeFirstRow();
                $sheet->fromModel($hiveExport);
            });
            $excel->sheet(__('export.inspections'), function($sheet) use ($inspExport) 
            {
                $sheet->setFreeze('D3');
                $sheet->setColumnFormat(array('I:Z' => '@'));
                $sheet->fromModel($inspExport);
            });

        })->store($fileType, storage_path('exports'));

        if (isset($file->storagePath))
        {
            $path = $file->storagePath.'/'.$fileName.'.'.$fileType;
            Mail::to($user->email)->send(new DataExport($path));
            $delf = $fileName.'.'.$fileType;
            $del  = Storage::disk('exports')->delete($delf);
            return response()->json(['file'=>$fileName.'.'.$fileType, 'del'=>$del, 'delf'=>$delf],200);
        }
        else
        {
            return response()->json(['error'=>'export-error'],404);
        }
    }
    
    private function getUser(User $user)
    {
        return $user->where('id',$user->id)->get()->map(function($item)
        {
            return [
                // __('export.id') => $item->id,
                __('export.name') => $item->name,
                __('export.email') => $item->email,
                __('export.avatar') => $item->avatar,
                __('export.created_at') => $item->created_at,
                __('export.updated_at') => $item->updated_at,
                __('export.last_login') => $item->last_login,
            ];
        });
    }

    private function getLocations(User $user)
    {
        return $user->locations()->withTrashed()->orderBy('deleted_at')->orderBy('name')->get()->map(function($item)
        {
            return [
                // __('export.id') => $item->id,
                __('export.name') => $item->name,
                __('export.type') => $item->type,
                __('export.hives') => $item->hives()->count(),
                __('export.coordinate_lat') => $item->coordinate_lat,
                __('export.coordinate_lon') => $item->coordinate_lon,
                __('export.address') => $item->street.' '.$item->street_no,
                __('export.postal_code') => $item->postal_code,
                __('export.city') => $item->city,
                __('export.country_code') => strtoupper($item->country_code),
                __('export.continent') => $item->continent,
                __('export.created_at') => $item->created_at,
                __('export.deleted_at') => $item->deleted_at,
            ];
        });
    }
    
    private function getHives(User $user)
    {
        return $user->hives()->withTrashed()->orderBy('deleted_at')->orderBy('location_id')->orderBy('name')->get()->map(function($item)
        {
            $queen = $item->queen;

            return [
                // __('export.id') => $item->id, 
                __('export.name') => $item->name,
                __('export.type') => $item->type,
                __('export.location') => $item->location,
                __('export.color') => $item->color,
                __('export.queen') => isset($queen) ? $queen->name : '',
                __('export.queen_color') => isset($queen) ? $queen->color : '',
                __('export.queen_born') => isset($queen) ? $queen->created_at : '',
                __('export.queen_fertilized') => isset($queen) ? $queen->fertilized : '',
                __('export.queen_clipped') => isset($queen) ? $queen->clipped : '',
                __('export.brood_layers') => $item->getBroodlayersAttribute(),
                __('export.honey_layers') => $item->getHoneylayersAttribute(),
                __('export.frames') => $item->frames()->count(),
                __('export.created_at') => $item->created_at,
                __('export.deleted_at') => $item->deleted_at,
            ];
        });
    }

    private function getInspections(User $user, $item_names)
    {
        // array of inspection items and data
        $inspection_data = array_fill_keys(array_map(function($name_arr)
        {
            return $name_arr['anc'].$name_arr['name'];

        }, $item_names),'');
        

        $inspections = $user->inspections()->withTrashed()->with('items')->orderBy('deleted_at')->orderByDesc('created_at')->get();


        $table = $inspections->map(function($inspection) use ($inspection_data)
        {
            if (isset($inspection->items))
            {
                foreach ($inspection->items as $inspectionItem)
                {
                    $array_key                   = $inspectionItem->anc.$inspectionItem->name;
                    $inspection_data[$array_key] = $inspectionItem->humanReadableValue();
                }
            }
            $locationName = ($inspection->locations()->count() > 0 ? $inspection->locations()->first()->name : ($inspection->hives()->count() > 0 ? $inspection->hives()->first()->location()->first()->name : ''));
            
            $reminder_date= '';
            if (isset($inspection->reminder_date) && $inspection->reminder_date != null)
            {
                $reminder_mom  = new Moment($inspection->reminder_date);
                $reminder_date = $reminder_mom->format('Y-m-d H:i:s');
            }

            $smileys  = __('taxonomy.smileys');
            $boolean  = __('taxonomy.boolean');
            
            $pre = [
                __('export.created_at') => $inspection->created_at,
                __('export.hive') => $inspection->hives()->count() > 0 ? $inspection->hives()->first()->name : '', 
                __('export.location') => $locationName, 
                __('export.impression') => $inspection->impression > -1 &&  $inspection->impression < count($smileys) ? $smileys[$inspection->impression] : '',
                __('export.attention') => $inspection->attention > -1 &&  $inspection->attention < count($boolean) ? $boolean[$inspection->attention] : '',
                __('export.reminder') => $inspection->reminder,
                __('export.reminder_date') => $reminder_date,
                __('export.notes') => $inspection->notes,
            ];

            $dat = array_merge($pre, $inspection_data, [__('export.deleted_at') => $inspection->deleted_at]);

            return $dat;
        });

        // Add extra title rows
        // $context = $inspection_data;
        $legends = $inspection_data;
        // $types   = $inspection_data;

        foreach ($item_names as $item) 
        {
            // if(in_array($item['name'], array_keys($context)))
            //     $context[$item['name']] = $item['anc'];

            if(in_array($item['name'], array_keys($legends)))
                $legends[$item['name']] = $item['range'];

            // if(in_array($item['name'], array_keys($types)))
            //     $types[$item['name']] = $item['type'];
        }

        $ins_cols = [
                __('export.created_at') => '',
                __('export.hive') => '', 
                __('export.location') => '', 
                __('export.impression') => '',
                __('export.attention') => '',
                __('export.reminder') => '',
                __('export.reminder_date') => '',
                __('export.notes') => '',
            ];

        $table->prepend(array_merge($ins_cols, $legends));
        //$table->prepend(array_merge($ins_cols,$types));
        // $table->prepend(array_merge($ins_cols, $context));

        return $table;
    }

    /**
    api/export/csv POST
    Generate a CSV measurement data export from InfluxDB. Make sure not to load a too large timespan (i.e. > 30 days), because the call will not succeed due to memory overload.
    @authenticated
    @bodyParam device_id required Device id to download data from
    @bodyParam start date required Date for start of data export. Example: 2020-05-27 16:16
    @bodyParam end date required Date for end of data export. Example: 2020-05-30 00:00
    @bodyParam separator string Symbol that should be used to separate columns in CSV file. Example: ;
    @bodyParam measurements string Comma separated list of measurement types to load. If you want a lot of data (i.e. > 30 days), make sure not to load more than one measurement. Example: 'am2315_t,am2315_h,mhz_co2'
    @bodyParam link boolean filled means: save the export to a file and provide the link, not filled means: output a text/html header with text containing the .csv content. Example:
    **/
    public function generate_csv(Request $request)
    {
        $device_id    = $request->input('device_id');
        $start        = $request->input('start');
        $end          = $request->input('end');
        $separator    = $request->input('separator', ';');
        $measurements = $request->input('measurements', '*');
        $device       = $request->user()->allDevices()->find($device_id);


        if ($device == null)
            return Response::json('invalid-user-device', 500);

        $sensor_key = $device->key;

        $options= ['precision'=>'rfc3339', 'format'=>'csv'];
        
        if ($measurements == null || $measurements == '' || $measurements === '*')
            $sensor_measurements = '*';
        else
            $sensor_measurements = '"'.implode('","',$measurements).'"';

        $query = 'SELECT '.$sensor_measurements.' FROM "sensors" WHERE ("key" = \''.$device->key.'\' OR "key" = \''.strtolower($device->key).'\' OR "key" = \''.strtoupper($device->key).'\') AND time >= \''.$start.'\' AND time < \''.$end.'\'';
        
        try{
            $client = new \Influx; 
            $data   = $client::query($query, $options)->getPoints(); // get first sensor date
        } catch (InfluxDB\Exception $e) {
            return Response::json('influx-query-error: '.$query, 500);
        }

        if (count($data) == 0)
            return Response::json('influx-query-empty', 500);

        // format CSV header row: time, sensor1 (unit2), sensor2 (unit2), etc. Excluse the 'sensor' and 'key' columns
        $csv_file = "";

        $csv_sens = array_diff(array_keys($data[0]),["sensor","key"]);
        $csv_head = [];
        foreach ($csv_sens as $sensor_name) 
        {
            $meas       = Measurement::where('abbreviation', $sensor_name)->first();
            $csv_head[] = $meas ? $meas->pq_name_unit().' ('.$sensor_name.')' : $sensor_name;
        }
        $csv_head = '"'.implode('"'.$separator.'"', $csv_head).'"'."\r\n";

        // format CSV file body
        $csv_body = [];
        foreach ($data as $sensor_values) 
        {
            $csv_body[] = implode($separator, array_diff_key($sensor_values,["sensor"=>0,"key"=>0]));
        }
        $csv_file = $csv_head.implode("\r\n", $csv_body);

        // return the CSV file content in a file on disk
        // $fileName = $device->name.'_'.$start.'_'.$end.'.csv';
        // Storage::disk('public')->put('/exports/'.$fileName, $csv_file);

        return response($csv_file)->header('Content-Type', 'text/html; charset=UTF-8');
    }

}
