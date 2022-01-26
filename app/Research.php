<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Image;
use Auth;
use DB;

class Research extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'researches';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['description', 'name', 'url', 'image_id', 'type', 'institution', 'type_of_data_used', 'start_date', 'end_date', 'user_id', 'default_user_ids'];
    protected $hidden   = ['users', 'deleted_at', 'user_id', 'owner', 'viewers'];
    protected $appends  = ['consent', 'consent_history', 'checklist_names', 'thumb_url'];

    protected $casts    = [
        'default_user_ids' => 'array'
    ];

    public static $pictureType = 'research';

    public static function storeImage($requestData)
    {
        return Image::store($requestData, Research::$pictureType);
    }

    public function getConsentAttribute()
    {
        $consent = DB::table('research_user')->where('research_id', $this->id)->where('user_id', Auth::user()->id)->orderBy('updated_at','desc')->limit(1)->value('consent');

        if ($consent === 1)
            return true;

        return false;
    }

    public function getConsentHistoryAttribute()
    {
        return DB::table('research_user')->where('research_id', $this->id)->where('user_id', Auth::user()->id)->orderBy('updated_at','desc')->get();
    }

    public function getChecklistNamesAttribute()
    {
        return $this->checklists()->pluck('name');
    }

    public function getThumbUrlAttribute()
    {
        if (isset($this->image_id))
            return isset($this->image->thumb_url) ? $this->image->thumb_url : null;

        return null;
    }
    
    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'research_user')->distinct('user_id');
    }

    public function flashlogs()
    {
        if (Auth::user()->researchesOwned->count() == 0)
            return null;

        $flashlogs = [];
        // Make dates
        $date_start = $this->start_date;
        $date_until = $this->end_date;
        
        // User specific data
        $user_consents = DB::table('research_user')->whereDate('updated_at', '<', $date_until)->where('consent', 1)->groupBy('user_id')->get()->toArray();

        die(print_r($user_consents));

        if (count($user_consents) == 0 || (count($user_consents) == 1 && $user_consent === 0)) // if only 1 and consent is false, stop
            return null;
        //die(print_r([$user_consents, $date_curr_consent, $date_next_consent, $index]));

        if ($item == 'measurements' || $item == 'weather')
            $this->cacheRequestRate('get-measurements-research');
        
        // Get all user data
        $user_apiaries     = Location::where('user_id', $user_id)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        $user_hives        = Hive::where('user_id', $user_id)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        $user_devices      = Device::with('sensorDefinitions')->where('user_id', $user_id)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        $user_flashlogs    = FlashLog::where('user_id', $user_id)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        $user_measurements = [];

        // add hive inspections (also from collaborators)
        $hive_inspection_ids = [];
        foreach ($user_hives as $hive)
        {
            $hive_inspections = $hive->inspections()->where('created_at', '>=', $date_start)->where('created_at', '<', $date_until)->get();
            foreach ($hive_inspections as $ins) 
                $hive_inspection_ids[] = $ins->id;
            
        }
        $hive_inspections = Inspection::whereIn('id', $hive_inspection_ids)->with('items')->where('created_at', '>=', $date_start)->where('created_at', '<', $date_until)->orderBy('created_at')->get();

        
        $data = [];

        foreach ($user_consents as $i => $consent) 
        {
            $user_consent       = $consent->consent;
            $date_curr_consent  = $consent->updated_at;
            
            if ($i < count($user_consents)-1)
            {
                $date_next_consent = $user_consents[$i+1]->updated_at;
                $next_consent      = $user_consents[$i+1]->consent;
            }
            else // until end of research no other consent filled
            {
                $date_next_consent = $date_until;
                $next_consent      = $user_consent;
            }

            // Minimize data to requested dates
            if ($request->filled('date_start') && $date_start > $date_curr_consent)
            {
                if ($date_start < $date_next_consent)
                    $date_curr_consent = $date_start;
                else
                    continue; // start >= next_consent, so hide this data from dataset, because earlier than requested
            }
                        
            if ($request->filled('date_until') && $date_until < $date_next_consent)
            {
                if ($date_until > $date_curr_consent)
                    $date_next_consent = $date_until;
                else
                    continue; // until <= curr_consent, so hide this data from dataset, because later than requested
            }

            // Fill objects for consent period
            if ($user_consent && ($next_consent || $i == 0))
            {    
                // add 
                switch($item)
                {
                    case 'apiaries':
                        $data = array_merge($data, $user_apiaries->where('created_at', '<=', $date_next_consent)->toArray());
                        break;
                    case 'hives':
                        $data = array_merge($data, $user_hives->where('created_at', '<=', $date_next_consent)->toArray());
                        break;
                    case 'devices':
                        $data = array_merge($data, $user_devices->where('created_at', '<=', $date_next_consent)->toArray());
                        break;
                    case 'inspections':
                        $data = array_merge($data, $hive_inspections->where('created_at', '>', $date_curr_consent)->where('created_at', '<=', $date_next_consent)->toArray());
                        break;
                    case 'flashlogs':
                        $data = array_merge($data, $user_flashlogs->where('created_at', '<=', $date_next_consent)->toArray());
                        break;
                    case 'measurements':
                        if ($user_devices->count() > 0)
                        {
                            foreach ($user_devices as $device)
                            {
                                if (($request->filled('device_id') && $request->input('device_id') == $device->id) || !$request->filled('device_id'))
                                {   
                                    if ($device->created_at < $date_next_consent)
                                    {
                                        $where= $device->influxWhereKeys().' AND time >= \''.$date_curr_consent.'\' AND time <= \''.$date_next_consent.'\'';
                                        $data = array_merge($data, $this->getArrayFromInflux($where, '*', 'sensors', $precision));
                                    }
                                }
                            }
                        }
                        break;
                    case 'weather':
                        if ($user_apiaries->count() > 0)
                        {
                            foreach ($user_apiaries as $apiary)
                            {
                                if ($apiary->created_at < $date_next_consent)
                                {
                                    $where= '"lat" = \''.$apiary->coordinate_lat.'\' AND "lon" = \''.$apiary->coordinate_lon.'\' AND time >= \''.$date_curr_consent.'\' AND time <= \''.$date_next_consent.'\'';
                                    $data = array_merge($data, $this->getArrayFromInflux($where, '*', 'weather', $precision));
                                }
                            }
                        }
                        break;
                    default:
                        return null;
                }
            }
        }
        return Response::json($flashlogs);

    }

    public function viewers()
    {
        return $this->belongsToMany(User::class, 'research_viewer');
    }

    public function checklists()
    {
        return $this->belongsToMany(Checklist::class, 'checklist_research');
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }
    
    public function delete()
    {
        // delete image 
        if(isset($this->image_id))
            $this->image()->delete();

        // delete the research
        return parent::delete();
    }

}
