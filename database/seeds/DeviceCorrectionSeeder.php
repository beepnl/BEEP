<?php

use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use App\Device;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class DeviceCorrectionSeeder extends Seeder
{
    
    public function run()
    {
        /*
        1. Get BEEP type devices from DB, that have a 20 character long hardware_id that starts with '0e'
        2. Get device specs from TTN, temporary store
        3. Delete device from TTN
        4. Create new device with same specs and without leading '0e' in $dev_id
        5. Change device hardware_id in database without leading '0e'
        */

        $devices = Device::where('category_id', 940)->get();

        foreach ($devices as $d) 
        {
            if (strlen($d->hardware_id) == 20 && strtolower(substr($d->hardware_id, 0, 2)) == '0e')
            {
                $ttn_device = $this->getTTNDevice($d->hardware_id);

                if ($ttn_device !== null)
                {
                    $dev_eui   = $ttn_device->lorawan_device->dev_eui;
                    $app_key   = $ttn_device->lorawan_device->app_key;
                    $dev_id    = substr($d->hardware_id, 2);

                    if (strlen($dev_eui) == 16 && strlen($app_key) == 32 && strlen($dev_id) == 18)
                    {
                        $ttn_device->dev_id = $dev_id;
                        $ttn_device->lorawan_device->dev_id  = $dev_id;
                        $ttn_device->lorawan_device->dev_eui = strtolower($dev_eui);
                        $ttn_device->lorawan_device->app_key = strtolower($app_key);

                        $this->deleteTTNDevice($d->hardware_id);
                        $response = $this->doTTNRequest($dev_id, 'POST', $ttn_device);

                        $ok = false;
                        if ($response->getStatusCode() == 200)
                        {
                            $d->hardware_id = $dev_id;
                            $d->save();
                            $ok = true;
                        }
                        print_r(['ok'=>$ok, 'ttn'=>(array)$ttn_device, 'd'=>$d->toArray()]);
                    }
                }
            }
        }
    }

    private function doTTNRequest($deviceId, $type='GET', $data=null)
    {
        $guzzle   = new Client();
        $url      = env('TTN_API_URL').'/applications/'.env('TTN_APP_NAME').'/devices/'.$deviceId;
        $response = null;

        try
        {
            $response = $guzzle->request($type, $url, ['headers'=>['Authorization'=>'Bearer '.env('TTN_API_KEY')], 'json' => $data]);
        }
        catch(RequestException $e)
        {
            if (!$e->hasResponse())
                return null;
            
            $response = $e->getResponse();
        }

        return $response;
    }
   
    private function getTTNDevice($dev_id)
    {
        
        $response = $this->doTTNRequest($dev_id);

        if ($response->getStatusCode() == 200)
            return json_decode($response->getBody());

        return null;
    }

    private function deleteTTNDevice($dev_id)
    {
        $response = $this->doTTNRequest($dev_id, 'DELETE');

        if ($response->getStatusCode() == 200)
            return json_decode($response->getBody());

        return null;
    }

}