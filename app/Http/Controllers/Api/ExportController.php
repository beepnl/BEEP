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
use App\Mail\DataExport;
use App\Exports\HiveExport;
use Moment\Moment;

class ExportController extends Controller
{
    public function all(Request $request)
    {
        $fileType = $request->has('fileFormat') ? $request->input('fileFormat') : 'xlsx';
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
            $excel->setCreator('BEEP')
                  ->setCompany('BEEP');

            // Call them separately
            $excel->setDescription($fileName);

            //Make sheets
            $excel->sheet(__('export.user'), function($sheet) use ($userExport) 
            {
                $sheet->freezeFirstRow();
                $sheet->fromModel($userExport);
            });
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
                $sheet->setFreeze('D4');
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
            return $name_arr['name'];

        }, $item_names),'');
        

        $inspections = $user->inspections()->withTrashed()->with('items')->orderBy('deleted_at')->orderByDesc('created_at')->get();


        $table = $inspections->map(function($inspection) use ($inspection_data)
        {
            if (isset($inspection->items))
            {
                foreach ($inspection->items as $inspectionItem)
                {
                    $inspection_data[$inspectionItem->name] = $inspectionItem->humanReadableValue();
                }
            }

            $locationName = $inspection->locations()->count() > 0 ? $inspection->locations()->first()->name : $inspection->hives()->count() > 0 ? $inspection->hives()->first()->location()->first()->name : '';
            
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
        $context = $inspection_data;
        $legends = $inspection_data;
        $types   = $inspection_data;

        foreach ($item_names as $item) 
        {
            if(in_array($item['name'], array_keys($context)))
                $context[$item['name']] = $item['anc'];

            if(in_array($item['name'], array_keys($legends)))
                $legends[$item['name']] = $item['range'];

            if(in_array($item['name'], array_keys($types)))
                $types[$item['name']] = $item['type'];
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
        $table->prepend(array_merge($ins_cols, $context));

        return $table;
    }

}
