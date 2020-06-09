<?php

use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use App\Device;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class TtnDeviceCorrectionSeeder extends Seeder
{
    
    public function run()
    {
        /*
        1. Get beep type devices from DB, that have a 20 character long hardware_id that starts with '0e'
        2. Get device specs from TTN, temporary store
        3. Delete device from TTN
        4. Create new device with same specs and without leading '0e' in $dev_id
        5. Change device hardware_id in database without leading '0e'
        */

        $devices = Device::all();

        foreach ($devices as $d) 
        {
            if (count($d->hardware_id) == 20 && strtolower(substr($d->hardware_id, 0, 2)) == '0e')
            {
                $new_hw_id  = substr($d->hardware_id, 2);
                $ttn_device = $this->getTTNDevice($d->hardware_id);

                if ($ttn_device)
                {
                    die(print_r($ttn_device));
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
            $response = $guzzle->request($type, $url, ['headers'=>['Authorization'=>'Key '.env('TTN_APP_KEY')], 'json' => $data]);
        }
        catch(RequestException $e)
        {
            if (!$e->hasResponse())
                return null;
            
            $response = $e->getResponse();
        }

        return $response;
    }
   
    /**
    api/devices/ttn/{dev_id} GET
    Get a TTN Device by Device ID (BEEP hardware_id)
    @authenticated
    */
    private function getTTNDevice($dev_id)
    {
        
        $response = $this->doTTNRequest($dev_id);

        if ($response->getStatusCode() == 200)
            return $response->getBody();

        return null;
    }
    /**
    api/devices/ttn/{dev_id} POST
    Create an OTAA LoRaWAN Device in the BEEP TTN Console by dev_id (dev_id (= BEEP hardware_id) a unique identifier for the device. It can contain lowercase letters, numbers, - and _) and this payload:
    {
      "lorawan_device": {
        "dev_eui": "<8 byte identifier for the device>", 
        "app_key": "<16 byte static key that is known by the device and the application. It is used for negotiating session keys (OTAA)>"
      }
    }
    @authenticated
    */
    private function deleteTTNDevice($dev_id)
    {
        $response = $this->doTTNRequest($dev_id, 'DELETE');

        if ($response->getStatusCode() == 200)
            return $response->getBody();

        return null;
    }

    private function createTTNDevice($dev_id, $dev_eui, $app_key)
    {
        $dev_id = strtolower($dev_id);

        $data = [
            "dev_id" => $dev_id,
            "lorawan_device"=>[
                "activation_constraints"=>"otaa",
                "app_id"=>env('TTN_APP_NAME'),
                "dev_eui"=>strtolower($dev_eui),
                "dev_id"=>$dev_id,
                "app_key"=>strtolower($app_key),
                "app_eui"=>env('TTN_APP_EUI')
            ]
        ];
        return $this->doTTNRequest($dev_id, 'POST', $data);
    }
}