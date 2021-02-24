<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class Webhook extends Model
{
    public static function sendNotification($message=null, $username='')
    {
        $url = env('WEBHOOK_URL', null);
        $usr = env('APP_NAME').' ('.env('APP_URL').') '.$username;
        if ($url && $message)
        {
            $body = [
                "username"  => $usr,
                "text"      => $message,
                "icon_url"  => "https://api.beep.nl/img/icons/icon_beep.png"
            ];
            $guzzle   = new Client();
            $response = $guzzle->request('POST', $url, ['body'=> json_encode($body), 'verify' => true, 'http_errors' => false]);
            
            return $response->getStatusCode();
        }
        return null;
    }
    
}
