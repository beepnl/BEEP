<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Auth;
use App\User;
use App\Device;
use App\Category;
use Validator;
use Response;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Moment\Moment;
use App\Translation;
use App\Inspection;
use App\InspectionItem;
use App\SensorDefinition;
/**
 * @group Api\DeviceController
 * Store and retreive Devices that produce measurements
 * @authenticated
 */
class DeviceController extends Controller
{
    private function doTTNRequest($deviceId=null, $type='GET', $data=null, $server=null)
    {
        $guzzle   = new Client();
        $server   = $server == null ? '' : '/'.$server;
        $deviceId = $deviceId == null ? '' : '/'.$deviceId;
        $url      = env('TTN_API_URL').$server.'/applications/'.env('TTN_APP_NAME').'/devices'.$deviceId;
        $response = null;

        //die(print_r([$url, $type, $server, json_encode($data)]));
        try
        {
            $response = $guzzle->request($type, $url, ['headers'=>['Authorization'=>'Bearer '.env('TTN_API_KEY')], 'json' => $data]);
        }
        catch(RequestException $e)
        {
            if (!$e->hasResponse())
                return Response::json('no_ttn_response', 500);
            
            $response = $e->getResponse();
        }

        return $response;
    }


    /**
    api/devices GET
    List all user Devices
    @authenticated
    @bodyParam hardware_id string Provide to filter on hardware_id
    @response [
        {
            "id": 1,
            "hive_id": 2,
            "name": "BEEPBASE-0000",
            "key": "000000000000000",
            "created_at": "2020-01-22 09:43:03",
            "last_message_received": null,
            "hardware_id": null,
            "firmware_version": null,
            "hardware_version": null,
            "boot_count": null,
            "measurement_interval_min": null,
            "measurement_transmission_ratio": null,
            "ble_pin": null,
            "battery_voltage": null,
            "next_downlink_message": null,
            "last_downlink_result": null,
            "type": "beep",
            "hive_name": "Hive 2",
            "location_name": "Test stand 1",
            "owner": true,
            "sensor_definitions": [
                {
                    "id": 7,
                    "name": null,
                    "inside": null,
                    "offset": 8131,
                    "multiplier": null,
                    "input_measurement_id": 7,
                    "output_measurement_id": 20,
                    "device_id": 1,
                    "input_abbr": "w_v",
                    "output_abbr": "weight_kg"
                }
            ]
        }
    ]
    */
    public function index(Request $request)
    {
        
        if ($request->filled('hardware_id'))
        {
            $hw_id   = strtolower($request->input('hardware_id'));
            $devices = $request->user()->allDevices()->where('hardware_id', $hw_id)->with('sensorDefinitions');

            // TODO: Exception for old hardware id's (including 0e as first byte) that have been stored, can be removed after implementation of issue #36 (correct hw_id in native apps, LoRa message parsers and database update of old id's)
            if ($devices->count() == 0 && strlen($hw_id) == 18)
                $devices = $request->user()->allDevices()->where('hardware_id', '0e'.$hw_id)->with('sensorDefinitions');
        }  
        else
        {
            $devices = $request->user()->allDevices()->with('sensorDefinitions');
        }

        // Check for device hijacking
        $reactNativeApp = false;
        if ($request->hasHeader('X-ClientId') && ($request->header('X-ClientId') == 'android' || $request->header('X-ClientId') == 'ios')) 
            $reactNativeApp = true;

        if ($devices->count() == 0)
        {
            if ($this->canUserClaimDeviceFromRequest($request, false, 'GET /devices') === false)
            {
                if ($reactNativeApp)
                    return Response::json(['info'=>'device_not_yours'], 200);

                return Response::json('device_not_yours', 403);
            }
            else if ($reactNativeApp)
            {
                return Response::json([], 200);
            }
            else if ($request->filled('hardware_id')) // Provide less confusing message to Android App listing of unexisting BEEP base
            {
                return Response::json('New BEEP base found', 404);
            }

            return Response::json('no_devices_found', 404);
        }

        return Response::json($devices->get());
    }

    /**
    api/devices/ttn/{dev_id} GET
    Get a BEEP TTS Cloud Device by Device ID (BEEP hardware_id)
    @authenticated
    */
    public function getTTNDevice(Request $request, $dev_id)
    {
        if ($this->canUserClaimDeviceFromRequest($request, false, '/devices/ttn/'.$dev_id) === false)
        {
            return Response::json("device_not_yours", 403);
        }

        $response = $this->doTTNRequest($dev_id);
        return Response::json(json_decode($response->getBody()), $response->getStatusCode());
    }
    /**
    api/devices/ttn/{dev_id} POST
    Create a BEEP TTS Cloud Device by Device ID, lorawan_device.dev_eui, and lorawan_device.app_key
    @authenticated
    */
    public function postTTNDevice(Request $request, $dev_id)
    {
        $validator = Validator::make($request->input(), [
            'lorawan_device.dev_eui' => 'required|alpha-num|size:16',
            'lorawan_device.app_key' => 'required|alpha-num|size:32'
        ]);

        if ($validator->fails())
            return Response::json(['errors'=>$validator->errors()], 422);

        $dev_eui = $request->input('lorawan_device.dev_eui');
        $app_key = $request->input('lorawan_device.app_key');

        if ($this->canUserClaimDevice(null, $dev_eui, $dev_id, true, 'postTTNDevice') === false)
            return Response::json("device_not_yours", 403);

        $response = $this->updateOrCreateTTNDevice($dev_id, $dev_eui, $app_key);
        return Response::json(json_decode($response->getBody()), $response->getStatusCode());
    }

    /**
    api/devices/tts/{step}/{dev_id}/{dev_eui} POST
    Debug BEEP TTS Cloud Device by lorawan_device.device_id, and lorawan_device.dev_eui
    @authenticated
    */
    public function debugTtsDevice(Request $request, $step, $dev_id, $dev_eui, $app_key=null)
    {
        if ($request->user()->hasRole('superadmin'))
        {
            $response = null;
            
            switch ($step) {
                case 'get':
                    $response = $this->doTTNRequest($dev_id);
                    //die(json_decode($response->getBody())->ids->dev_eui);
                    break;
                case 'delete_ns':
                    $response = $this->doTTNRequest($dev_id, 'DELETE', null, 'ns');
                    break;
                case 'delete_as':
                    $response = $this->doTTNRequest($dev_id, 'DELETE', null, 'as');
                    break;
                case 'delete_js':
                    $response = $this->doTTNRequest($dev_id, 'DELETE', null, 'js');
                    break;
                case 'delete':
                    $response = $this->doTTNRequest($dev_id, 'DELETE');
                    break;
                case 'create':
                    $response = $this->createApplicationDevice($dev_id, $dev_eui);
                    break;
                case 'network':
                    $response = $this->linkDeviceToNetworkServer($dev_id, $dev_eui);
                    break;
                case 'application':
                    $response = $this->linkDeviceToApplicationServer($dev_id, $dev_eui);
                    break;
                case 'join':
                    $response = $this->linkDeviceToJoinServer($dev_id, $dev_eui, $app_key);
                    break;
            }

            if ($response)
                return Response::json(json_decode($response->getBody()), $response->getStatusCode());
        }
        return Response::json('debug_error', 500);
    }

    private function createApplicationDevice($dev_id, $dev_eui)
    {
        $data = [
            "end_device" => [
                "ids" => [
                    "device_id" => $dev_id,
                    "dev_eui"   => $dev_eui,
                    "join_eui"  => env('TTN_APP_EUI')
                ],
                "join_server_address"       => env('TTN_APP_URL'),
                "network_server_address"    => env('TTN_APP_URL'),
                "application_server_address"=> env('TTN_APP_URL')
            ],
            "field_mask" => [
                    "paths" => [
                        "join_server_address",
                        "network_server_address",
                        "application_server_address",
                        "ids.dev_eui",
                        "ids.join_eui"
                    ]
                ]
            ];

        return $this->doTTNRequest(null, 'POST', $data);
    }

    private function linkDeviceToNetworkServer($dev_id, $dev_eui)
    {
        $data = [
            "end_device" => [
                "multicast" => false,
                "supports_join" => true,
                "lorawan_version" => "MAC_V1_0_2",
                "ids" => [
                    "device_id" => $dev_id,
                    "dev_eui"   => $dev_eui,
                    "join_eui"  => env('TTN_APP_EUI')
                ],
                "mac_settings" => [
                    "supports_32_bit_f_cnt" => true,
                    "rx2_data_rate_index" => 0,
                    "rx2_frequency" => 869525000
                ],
                "supports_class_c" => false,
                "supports_class_b" => false,
                "lorawan_phy_version" => "PHY_V1_0_2_REV_A",
                "frequency_plan_id" => "EU_863_870_TTN"
            ],
            "field_mask" => [
                    "paths" => [
                        "multicast",
                        "supports_join",
                        "lorawan_version",
                        "ids.device_id",
                        "ids.dev_eui",
                        "ids.join_eui",
                        "mac_settings.supports_32_bit_f_cnt",
                        "mac_settings.rx2_data_rate_index",
                        "mac_settings.rx2_frequency",
                        "supports_class_c",
                        "supports_class_b",
                        "lorawan_phy_version",
                        "frequency_plan_id"
                    ]
                ]
            ];

        return $this->doTTNRequest($dev_id, 'PUT', $data, 'ns');
    }

    private function linkDeviceToApplicationServer($dev_id, $dev_eui)
    {
        $data = [
            "end_device" => [
                "ids" => [
                    "device_id" => $dev_id,
                    "dev_eui"   => $dev_eui,
                    "join_eui"  => env('TTN_APP_EUI')
                ]
            ],
            "field_mask" => [
                    "paths" => [
                        "ids.device_id",
                        "ids.dev_eui",
                        "ids.join_eui"
                    ]
                ]
            ];

        return $this->doTTNRequest($dev_id, 'PUT', $data, 'as');
    }

    private function linkDeviceToJoinServer($dev_id, $dev_eui, $app_key)
    {
        $data = [
            "end_device" => [
                "ids" => [
                    "device_id" => $dev_id,
                    "dev_eui"   => $dev_eui,
                    "join_eui"  => env('TTN_APP_EUI')
                ],
                "network_server_address" => env('TTN_APP_URL'),
                "application_server_address" => env('TTN_APP_URL'),
                "root_keys" => [
                    "app_key" => [
                        "key" => $app_key
                    ]
                ]
            ],
            "field_mask" => [
                    "paths" => [
                        "network_server_address",
                        "application_server_address",
                        "ids.device_id",
                        "ids.dev_eui",
                        "ids.join_eui",
                        "root_keys.app_key.key"
                    ]
                ]
            ];

        return $this->doTTNRequest($dev_id, 'PUT', $data, 'js');
    }

    private function updateOrCreateTTNDevice($dev_id, $dev_eui, $app_key, $server='')
    {
        $dev_id  = strtolower($dev_id);
        $dev_eui = strtolower($dev_eui);

        $device_check = $this->doTTNRequest($dev_id);
        if ($device_check->getStatusCode() == 200) // if device exists, delete device to renew settings
        {
            // Add former Dev EUI to Device
            $device = Device::where('hardware_id', $dev_id)->first();
            if ($device)
            {
                $former_tts_device = json_decode($device_check->getBody());
                if ($former_tts_device && isset($former_tts_device->ids->dev_eui))
                    $device->addFormerKey($former_tts_device->ids->dev_eui);
            }

            // Delete js, as, ns, and device first
            $this->doTTNRequest($dev_id, 'DELETE', null, 'js');
            $this->doTTNRequest($dev_id, 'DELETE', null, 'as');
            $this->doTTNRequest($dev_id, 'DELETE', null, 'ns');
            $delete = $this->doTTNRequest($dev_id, 'DELETE');
            if ($delete->getStatusCode() != 200) // if 200 ok (deleted) go on re-creating the device with other settings
                return $delete;
        }

        $step1 = $this->createApplicationDevice($dev_id, $dev_eui);
        if ($step1->getStatusCode() == 200 || $step1->getStatusCode() == 201)
        {
            $step2 = $this->linkDeviceToNetworkServer($dev_id, $dev_eui);
            if ($step2->getStatusCode() == 200 || $step2->getStatusCode() == 201)
            {
                $step3 = $this->linkDeviceToApplicationServer($dev_id, $dev_eui);
                if ($step3->getStatusCode() == 200 || $step3->getStatusCode() == 201)
                {
                    return $this->linkDeviceToJoinServer($dev_id, $dev_eui, $app_key);
                }
                else
                {
                    return $step3;
                }   
            }
            else
            {
                return $step2;
            }   
        }
        else
        {
            return $step1;
        }
    }

    private function makeCoverageCheckDeviceId($provider, Device $device)
    {
        $hardware_id = $device->hardware_id ? $device->hardware_id : 'device-'.$device->id;
        $dev_id      = strtolower($provider.'-coverage-'.$device->id.'-'.$hardware_id);
        $dev_id      = preg_replace('/[^a-z0-9-]/', '-', $dev_id);
        $dev_id      = preg_replace('/-+/', '-', $dev_id);
        $dev_id      = trim($dev_id, '-');

        return substr($dev_id, 0, 36);
    }

    private function makeHeliumDeviceId(Device $device)
    {
        $hardware_id = $device->hardware_id ? $device->hardware_id : 'device-'.$device->id;
        $dev_id      = strtolower('helium-'.$device->id.'-'.$hardware_id);
        $dev_id      = preg_replace('/[^a-z0-9-]/', '-', $dev_id);
        $dev_id      = preg_replace('/-+/', '-', $dev_id);
        $dev_id      = trim($dev_id, '-');

        return substr($dev_id, 0, 36);
    }

    private function responseBody($response)
    {
        if (method_exists($response, 'getBody'))
            return (string) $response->getBody();

        if (method_exists($response, 'getContent'))
            return $response->getContent();

        return '';
    }

    private function responseJsonBody($response)
    {
        $body = $this->responseBody($response);
        $json = json_decode($body);

        return $json ?: $body;
    }

    private function envFlag($key, $default=false)
    {
        return filter_var(env($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    private function heliumDeviceName(Device $device, $purpose='device')
    {
        $name = $device->name ?: 'BEEP base '.$device->id;
        if ($purpose == 'coverage_check')
            $name .= ' coverage check';

        return substr($name, 0, 100);
    }

    private function heliumApiError($error, $message, $status=501)
    {
        return Response::json([
            'error' => $error,
            'message' => $message,
        ], $status);
    }

    private function heliumUrl($base_url, $path)
    {
        return rtrim($base_url, '/').'/'.ltrim($path, '/');
    }

    protected function makeHeliumHttpClient()
    {
        return new Client();
    }

    private function doHeliumRequest($method, $url, $headers=[], $json=null)
    {
        $guzzle = $this->makeHeliumHttpClient();
        $options = [
            'headers' => array_merge(['Content-Type' => 'application/json'], $headers),
        ];

        if ($json !== null)
            $options['json'] = $json;

        try
        {
            return $guzzle->request($method, $url, $options);
        }
        catch(RequestException $e)
        {
            if (!$e->hasResponse())
                return Response::json('no_helium_response', 500);

            return $e->getResponse();
        }
    }

    private function heliumProvisionDriver($purpose='device')
    {
        $driver = env('HELIUM_PROVISION_DRIVER');
        if ($driver)
            return $driver;

        foreach (['chirpstack_rest', 'legacy_console', 'webhook'] as $candidate)
        {
            if ($this->isHeliumProvisionConfigured($candidate, $purpose))
                return $candidate;
        }

        if (env('HELIUM_CHIRPSTACK_REST_URL'))
            return 'chirpstack_rest';

        if (env('HELIUM_CONSOLE_API_KEY'))
            return 'legacy_console';

        if (env('HELIUM_PROVISION_URL') || env('HELIUM_COVERAGE_PROVISION_URL'))
            return 'webhook';

        return 'not_configured';
    }

    private function isHeliumProvisionConfigured($driver=null, $purpose='device')
    {
        $driver = $driver ?: $this->heliumProvisionDriver($purpose);

        return count($this->heliumMissingConfig($driver, $purpose)) == 0;
    }

    private function heliumMissingConfig($driver, $purpose='device')
    {
        if ($driver == 'chirpstack_rest')
        {
            return array_values(array_filter([
                env('HELIUM_CHIRPSTACK_REST_URL') ? null : 'HELIUM_CHIRPSTACK_REST_URL',
                env('HELIUM_CHIRPSTACK_API_TOKEN') ? null : 'HELIUM_CHIRPSTACK_API_TOKEN',
                env('HELIUM_CHIRPSTACK_APPLICATION_ID') ? null : 'HELIUM_CHIRPSTACK_APPLICATION_ID',
                env('HELIUM_CHIRPSTACK_DEVICE_PROFILE_ID') ? null : 'HELIUM_CHIRPSTACK_DEVICE_PROFILE_ID',
            ]));
        }

        if ($driver == 'legacy_console')
            return env('HELIUM_CONSOLE_API_KEY') ? [] : ['HELIUM_CONSOLE_API_KEY'];

        if ($driver == 'webhook')
        {
            if ($purpose == 'coverage_check')
                return (env('HELIUM_COVERAGE_PROVISION_URL') || env('HELIUM_PROVISION_URL')) ? [] : ['HELIUM_COVERAGE_PROVISION_URL'];

            return env('HELIUM_PROVISION_URL') ? [] : ['HELIUM_PROVISION_URL'];
        }

        return ['HELIUM_PROVISION_DRIVER'];
    }

    /**
    api/devices/lorawan/providers GET
    Return LoRaWAN provider capabilities for the current API environment.
    @authenticated
    */
    public function lorawanProviders(Request $request)
    {
        $driver = $this->heliumProvisionDriver();
        $coverage_driver = $this->heliumProvisionDriver('coverage_check');

        return Response::json([
            'ttn' => [
                'can_provision' => !!(env('TTN_API_URL') && env('TTN_API_KEY') && env('TTN_APP_NAME') && env('TTN_APP_EUI')),
                'can_coverage_check' => !!(env('TTN_API_URL') && env('TTN_API_KEY') && env('TTN_APP_NAME') && env('TTN_APP_EUI')),
            ],
            'helium' => [
                'can_provision' => $this->isHeliumProvisionConfigured($driver),
                'can_coverage_check' => $this->isHeliumProvisionConfigured($coverage_driver, 'coverage_check'),
                'driver' => $driver,
                'coverage_driver' => $coverage_driver,
                'missing_config' => $this->heliumMissingConfig($driver),
                'missing_coverage_config' => $this->heliumMissingConfig($coverage_driver, 'coverage_check'),
                'http_integration_managed' => $driver == 'chirpstack_rest' ? $this->envFlag('HELIUM_CHIRPSTACK_CONFIGURE_HTTP', false) : null,
            ],
        ], 200);
    }

    private function provisionHeliumWithChirpStack($dev_id, $dev_eui, $app_eui, $app_key, Device $device, $purpose='device')
    {
        $base_url          = env('HELIUM_CHIRPSTACK_REST_URL');
        $token             = env('HELIUM_CHIRPSTACK_API_TOKEN');
        $application_id    = env('HELIUM_CHIRPSTACK_APPLICATION_ID');
        $device_profile_id = env('HELIUM_CHIRPSTACK_DEVICE_PROFILE_ID');

        if (!$base_url || !$token || !$application_id || !$device_profile_id)
        {
            return $this->heliumApiError(
                'helium_chirpstack_not_configured',
                'Configure HELIUM_CHIRPSTACK_REST_URL, HELIUM_CHIRPSTACK_API_TOKEN, HELIUM_CHIRPSTACK_APPLICATION_ID, and HELIUM_CHIRPSTACK_DEVICE_PROFILE_ID on the API server to enable Helium/OpenLNS provisioning.'
            );
        }

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Grpc-Metadata-Authorization' => 'Bearer '.$token,
        ];

        if ($this->envFlag('HELIUM_CHIRPSTACK_CONFIGURE_HTTP', false))
        {
            $event_url = env('HELIUM_CHIRPSTACK_HTTP_EVENT_URL') ?: url('api/lora_sensors');
            $integration = [
                'integration' => [
                    'applicationId' => $application_id,
                    'encoding' => 'JSON',
                    'eventEndpointUrl' => $event_url,
                ],
            ];

            $response = $this->doHeliumRequest(
                'PUT',
                $this->heliumUrl($base_url, 'api/applications/'.$application_id.'/integrations/http'),
                $headers,
                $integration
            );

            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300)
            {
                $response = $this->doHeliumRequest(
                    'POST',
                    $this->heliumUrl($base_url, 'api/applications/'.$application_id.'/integrations/http'),
                    $headers,
                    $integration
                );

                if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300)
                    return $response;
            }
        }

        $this->doHeliumRequest('DELETE', $this->heliumUrl($base_url, 'api/devices/'.$dev_eui), $headers);

        $device_response = $this->doHeliumRequest(
            'POST',
            $this->heliumUrl($base_url, 'api/devices'),
            $headers,
            [
                'device' => [
                    'devEui' => $dev_eui,
                    'joinEui' => $app_eui,
                    'name' => $this->heliumDeviceName($device, $purpose),
                    'description' => 'BEEP API provisioned '.$purpose.' for device '.$device->id.' / '.$device->hardware_id,
                    'applicationId' => $application_id,
                    'deviceProfileId' => $device_profile_id,
                    'skipFcntCheck' => false,
                    'isDisabled' => false,
                    'tags' => [
                        'beep_device_id' => (string) $device->id,
                        'beep_hardware_id' => (string) $device->hardware_id,
                        'beep_lorawan_id' => $dev_id,
                        'purpose' => $purpose,
                        'provider' => 'helium',
                    ],
                ],
            ]
        );

        if ($device_response->getStatusCode() < 200 || $device_response->getStatusCode() >= 300)
            return $device_response;

        return $this->doHeliumRequest(
            'POST',
            $this->heliumUrl($base_url, 'api/devices/'.$dev_eui.'/keys'),
            $headers,
            [
                'deviceKeys' => [
                    'devEui' => $dev_eui,
                    'nwkKey' => $app_key,
                ],
            ]
        );
    }

    private function getOrCreateLegacyHeliumLabel($base_url, $headers)
    {
        $label_id = env('HELIUM_CONSOLE_LABEL_ID');
        if ($label_id)
            return $label_id;

        $label_name = env('HELIUM_CONSOLE_LABEL_NAME');
        if (!$label_name)
            return null;

        $label_name = strtoupper($label_name);
        $labels_response = $this->doHeliumRequest('GET', $this->heliumUrl($base_url, 'api/v1/labels'), $headers);
        if ($labels_response->getStatusCode() >= 200 && $labels_response->getStatusCode() < 300)
        {
            $labels = json_decode($this->responseBody($labels_response));
            if (is_array($labels))
            {
                foreach ($labels as $label)
                {
                    if (isset($label->id) && isset($label->name) && strtoupper($label->name) == $label_name)
                        return $label->id;
                }
            }
        }

        $label_response = $this->doHeliumRequest('POST', $this->heliumUrl($base_url, 'api/v1/labels'), $headers, ['name' => $label_name]);
        if ($label_response->getStatusCode() < 200 || $label_response->getStatusCode() >= 300)
            return $label_response;

        $label = json_decode($this->responseBody($label_response));
        return isset($label->id) ? $label->id : null;
    }

    private function provisionHeliumWithLegacyConsole($dev_id, $dev_eui, $app_eui, $app_key, Device $device, $purpose='device')
    {
        $base_url = env('HELIUM_CONSOLE_API_URL') ?: 'https://console.helium.com';
        $api_key  = env('HELIUM_CONSOLE_API_KEY');

        if (!$api_key)
        {
            return $this->heliumApiError(
                'helium_console_not_configured',
                'Configure HELIUM_CONSOLE_API_KEY on the API server to enable legacy Helium Console provisioning.'
            );
        }

        $headers = ['key' => $api_key];
        $device_response = $this->doHeliumRequest(
            'POST',
            $this->heliumUrl($base_url, 'api/v1/devices'),
            $headers,
            [
                'app_eui' => $app_eui,
                'app_key' => $app_key,
                'dev_eui' => $dev_eui,
                'name' => $this->heliumDeviceName($device, $purpose),
            ]
        );

        if ($device_response->getStatusCode() < 200 || $device_response->getStatusCode() >= 300)
            return $device_response;

        $device_body = json_decode($this->responseBody($device_response));
        $console_device_id = isset($device_body->id) ? $device_body->id : null;
        $label = $this->getOrCreateLegacyHeliumLabel($base_url, $headers);

        if ($label && is_object($label) && method_exists($label, 'getStatusCode'))
            return $label;

        if ($console_device_id && $label)
        {
            $label_response = $this->doHeliumRequest(
                'POST',
                $this->heliumUrl($base_url, 'api/v1/devices/'.$console_device_id.'/labels'),
                $headers,
                ['label' => $label]
            );

            if ($label_response->getStatusCode() < 200 || $label_response->getStatusCode() >= 300)
                return $label_response;
        }

        return $device_response;
    }

    private function provisionHeliumWithWebhook($dev_id, $dev_eui, $app_eui, $app_key, Device $device, $purpose='device')
    {
        $url = $purpose == 'coverage_check'
            ? (env('HELIUM_COVERAGE_PROVISION_URL') ?: env('HELIUM_PROVISION_URL'))
            : env('HELIUM_PROVISION_URL');

        if ($url == null || $url == '')
        {
            return $this->heliumApiError(
                'helium_provisioner_not_configured',
                'Configure HELIUM_PROVISION_URL on the API server to enable Helium device provisioning.'
            );
        }

        $headers = [];
        $token = env('HELIUM_PROVISION_TOKEN') ?: env('HELIUM_COVERAGE_PROVISION_TOKEN');
        if ($token != null && $token != '')
            $headers['Authorization'] = 'Bearer '.$token;

        return $this->doHeliumRequest('POST', $url, $headers, [
            'purpose' => $purpose,
            'network_device_id' => $dev_id,
            'device_id' => $device->id,
            'hardware_id' => $device->hardware_id,
            'name' => $this->heliumDeviceName($device, $purpose),
            'dev_eui' => $dev_eui,
            'app_eui' => $app_eui,
            'app_key' => $app_key,
            'uplink_url' => url('api/lora_sensors'),
            'payload_format' => 'helium_http_raw',
            'integration' => [
                'name' => 'BEEP lora_sensors',
                'type' => 'http',
                'decoder' => 'raw',
            ],
        ]);
    }

    private function provisionHeliumDevice($dev_id, $dev_eui, $app_eui, $app_key, Device $device, $purpose='device')
    {
        $driver = $this->heliumProvisionDriver($purpose);

        if ($driver == 'chirpstack_rest')
            return $this->provisionHeliumWithChirpStack($dev_id, $dev_eui, $app_eui, $app_key, $device, $purpose);

        if ($driver == 'legacy_console')
            return $this->provisionHeliumWithLegacyConsole($dev_id, $dev_eui, $app_eui, $app_key, $device, $purpose);

        if ($driver == 'webhook' || $driver == 'not_configured')
            return $this->provisionHeliumWithWebhook($dev_id, $dev_eui, $app_eui, $app_key, $device, $purpose);

        return $this->heliumApiError(
            'invalid_helium_provision_driver',
            'Configure HELIUM_PROVISION_DRIVER as chirpstack_rest, legacy_console, or webhook.'
        );
    }

    /**
    api/devices/{id}/lorawan/helium POST
    Provision a BEEP base in the BEEP Helium account and return LoRaWAN credentials for the mobile app to write over Bluetooth.
    @authenticated
    @bodyParam dev_eui string optional 16 hexadecimal characters. Generated when omitted.
    @bodyParam app_eui string optional 16 hexadecimal characters. HELIUM_APP_EUI is used when omitted and configured.
    @bodyParam app_key string optional 32 hexadecimal characters. Generated when omitted.
    */
    public function heliumLorawan(Request $request, $id)
    {
        $validator = Validator::make($request->input(), [
            'dev_eui' => ['nullable', 'regex:/^[0-9a-fA-F]{16}$/'],
            'app_eui' => ['nullable', 'regex:/^[0-9a-fA-F]{16}$/'],
            'app_key' => ['nullable', 'regex:/^[0-9a-fA-F]{32}$/'],
        ]);

        if ($validator->fails())
            return Response::json(['errors'=>$validator->errors()], 422);

        $device = $request->user()->allDevices()->findOrFail($id);
        if (!$device->hardware_id)
            return Response::json(['error' => 'device_has_no_hardware_id'], 422);

        $dev_id  = $this->makeHeliumDeviceId($device);
        $dev_eui = strtolower($request->input('dev_eui', bin2hex(random_bytes(8))));
        $app_key = strtolower($request->input('app_key', bin2hex(random_bytes(16))));
        $app_eui = strtolower($request->input('app_eui') ?: env('HELIUM_APP_EUI') ?: bin2hex(random_bytes(8)));

        if (!preg_match('/^[0-9a-f]{16}$/', $app_eui))
            return Response::json(['error' => 'invalid_or_missing_app_eui'], 422);

        $response = $this->provisionHeliumDevice($dev_id, $dev_eui, $app_eui, $app_key, $device);
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300)
            return Response::json($this->responseJsonBody($response), $response->getStatusCode());

        if ($device->key && strtolower($device->key) !== $dev_eui)
            $device->addFormerKey($device->key);

        $device->key = $dev_eui;
        $device->save();

        return Response::json([
            'provider' => 'helium',
            'device_id' => $device->id,
            'hardware_id' => $device->hardware_id,
            'network_device_id' => $dev_id,
            'dev_eui' => $dev_eui,
            'app_eui' => $app_eui,
            'app_key' => $app_key,
            'uplink_url' => url('api/lora_sensors'),
            'join_timeout_seconds' => 180,
        ], 200);
    }

    /**
    api/devices/{id}/lorawan/coverage-check POST
    Provision temporary LoRaWAN credentials for checking network coverage.
    @authenticated
    @bodyParam provider string required One of: ttn, helium
    @bodyParam dev_eui string optional 16 hexadecimal characters. Generated when omitted.
    @bodyParam app_eui string optional 16 hexadecimal characters. TTN uses TTN_APP_EUI when omitted.
    @bodyParam app_key string optional 32 hexadecimal characters. Generated when omitted.
    */
    public function coverageCheck(Request $request, $id)
    {
        $validator = Validator::make($request->input(), [
            'provider' => ['required', Rule::in(['ttn', 'helium'])],
            'dev_eui' => ['nullable', 'regex:/^[0-9a-fA-F]{16}$/'],
            'app_eui' => ['nullable', 'regex:/^[0-9a-fA-F]{16}$/'],
            'app_key' => ['nullable', 'regex:/^[0-9a-fA-F]{32}$/'],
        ]);

        if ($validator->fails())
            return Response::json(['errors'=>$validator->errors()], 422);

        $device = $request->user()->allDevices()->findOrFail($id);
        if (!$device->hardware_id)
            return Response::json(['error' => 'device_has_no_hardware_id'], 422);

        $provider = strtolower($request->input('provider'));
        $dev_id   = $this->makeCoverageCheckDeviceId($provider, $device);
        $dev_eui  = strtolower($request->input('dev_eui', bin2hex(random_bytes(8))));
        $app_key  = strtolower($request->input('app_key', bin2hex(random_bytes(16))));
        $app_eui  = strtolower($request->input('app_eui') ?: ($provider == 'ttn' ? env('TTN_APP_EUI') : (env('HELIUM_APP_EUI') ?: bin2hex(random_bytes(8)))));

        if (!preg_match('/^[0-9a-f]{16}$/', $app_eui))
            return Response::json(['error' => 'invalid_or_missing_app_eui'], 422);

        if ($provider == 'ttn')
        {
            $response = $this->updateOrCreateTTNDevice($dev_id, $dev_eui, $app_key);
        }
        else
        {
            $response = $this->provisionHeliumDevice($dev_id, $dev_eui, $app_eui, $app_key, $device, 'coverage_check');
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300)
            return Response::json($this->responseJsonBody($response), $response->getStatusCode());

        return Response::json([
            'provider' => $provider,
            'device_id' => $device->id,
            'hardware_id' => $device->hardware_id,
            'network_device_id' => $dev_id,
            'dev_eui' => $dev_eui,
            'app_eui' => $app_eui,
            'app_key' => $app_key,
            'uplink_url' => url('api/lora_sensors'),
            'join_timeout_seconds' => 180,
            'persisted' => false,
        ], 200);
    }

    private function canUserClaimDevice($id=null, $key=null, $hwi=null, $undeleteTrashed=true, $from='')
    {
        $can_claim     = 0;
        $device_exists = 0;
        $user_devices  = Auth::user()->devices; // device collection
        $user_id       = Auth::user()->id;
        
        if (isset($id))
        {
            $device_exists += Device::withTrashed()->where('id', $id)->count();
            $can_claim += $user_devices->where('id', $id)->count();
        }
        
        if (isset($key))
        {
            $device_exists += Device::where('key', $key)->count();
            $can_claim += $user_devices->where('key', $key)->count();
        }
        
        if (isset($hwi))
        {
            $device_exists += Device::withTrashed()->where('hardware_id', $hwi)->count();
            $can_claim += $user_devices->where('hardware_id', $hwi)->count();
        }

        // 
        if ($can_claim == 0 && $device_exists > 0)
        {
            // $device is probably deleted 
            $device = Device::onlyTrashed()->where('hardware_id', $hwi)->orWhere('id', $id)->orWhere('key', $key)->first();
            if ($device)
            {
                if ($device->user_id === $user_id) // If deleted device is owned by user, undelete it
                {
                    $deleted_date = $device->deleted_at;
                    $device->restore();
                    $can_claim = 1;
                    Log::info("UserID=$user_id (device->user_id=$device->user_id) restored deleted Device with: ID=$device->id, HWI=$hwi, KEY=$key (from: $from)");
                    
                    // also restore trashed sensordefinitions if available
                    $sensor_definitions_deleted = SensorDefinition::onlyTrashed()->where('device_id', $device->id)->where('deleted_at', $deleted_date)->get();
                    $sensor_def_count           = $sensor_definitions_deleted->count();
                    if ($sensor_def_count > 0)
                    {
                        foreach ($sensor_definitions_deleted as $sd)
                            $sd->restore();

                        Log::info("UserID=$user_id restored $sensor_def_count sensor definitions from Device ID=$device->id (from: $from)");
                    }
                    
                }
                else // reset device and assign to user
                {
                    $device->restore(); // is required in stead of directly save, to prevent PDO error Duplicate entry sensors.sensors_key_unique
                    $device->log_file_info = null;
                    $device->former_key_list = null;
                    $device->hive_id = null;
                    $device->user_id = $user_id;
                    $device->name = 'DEVICE '.strtoupper(substr($key, -4));
                    $device->save();
                    $can_claim = 1;
                    Log::info("UserID=$user_id claimed deleted Device with: ID=$id, HWI=$hwi, KEY=$key (from: $from)");
                }
            }
            // else if $can_claim == 0 device exists, but is bound to another user   
        }

        if ($can_claim > 0 || $device_exists == 0)
            return true;

        Log::error("UserID=$user_id cannot claim DeviceID=$id: HWI=$hwi, KEY=$key (from: $from)");
        
        return false;
    }

    private function canUserClaimDeviceFromRequest(Request $request, $undeleteTrashed=true, $from='canUserClaimDeviceFromRequest')
    {
        $id  = $request->filled('id') ? $request->input('id') : null;
        $key = $request->filled('key') ? strtolower($request->input('key')) : null;
        $hwi = $request->filled('hardware_id') ? strtolower($request->input('hardware_id')) : null;
        
        return $this->canUserClaimDevice($id, $key, $hwi, $undeleteTrashed, $from);
    }



    /**
    api/devices/{id} GET
    List one Device by id
    @authenticated
    */
    public function show(Request $request, $id)
    {
        $device = $request->user()->allDevices()->with('sensorDefinitions')->findOrFail($id);
        
        if ($device)
            return Response::json($device);

        return Response::json('no_devices_found', 404);
    }

    /**
    api/devices POST
    Create or Update a Device
    @authenticated
    @bodyParam id integer Device id to update. (Required without key and hardware_id)
    @bodyParam key string DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint. (Required without id and hardware_id)
    @bodyParam hardware_id string Hardware id of the device as device name in TTN. (Required without id and key)
    @bodyParam name string Device name
    @bodyParam hive_id integer Hive that the sensor is measuring. Default: null
    @bodyParam type string Category name of the hive type from the Categories table. Default: beep
    @bodyParam last_message_received timestamp Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    @bodyParam firmware_version string Firmware version of the Device
    @bodyParam hardware_version string Hardware version of the Device
    @bodyParam boot_count integer Amount of boots of the Device
    @bodyParam measurement_interval_min float Measurement interval in minutes
    @bodyParam measurement_transmission_ratio float Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    @bodyParam ble_pin string Bleutooth PIN of Device: 6 numbers between 0-9
    @bodyParam battery_voltage float Last measured battery voltage
    @bodyParam next_downlink_message string Hex string to send via downlink at next connection (LoRaWAN port 6)
    @bodyParam last_downlink_result string Result received from BEEP base after downlink message (LoRaWAN port 5)
    @bodyParam create_ttn_device boolean If true, create a new LoRaWAN device in the BEEP TTN console. If succesfull, create the device.
    @bodyParam app_key string BEEP base LoRaWAN application key that you would like to store in TTN
    */

    public function store(Request $request)
    {

        $device_array = $request->input();
        
        if ($request->filled('create_ttn_device') && $request->input('create_ttn_device') == true && $request->filled('hardware_id'))
        {

            if ($request->user()->hasRole(['superadmin', 'admin']) == false)
            {
                if ($this->canUserClaimDeviceFromRequest($request, true, 'POST /devices') === false)
                    return Response::json("device_not_yours", 403);

                $device_count = Device::where('user_id', $request->user()->id)->count();
                if ($device_count > 50)
                    return Response::json("max_ttn_devices_reached_please_request_more", 403);

            }

            $dev_id  = strtolower($request->input('hardware_id'));
            $dev_eui = $request->filled('key') ? $request->input('key') : bin2hex(random_bytes(8)); // doubles output length
            $app_key = $request->filled('app_key') ? $request->input('app_key') : bin2hex(random_bytes(16)); // doubles output length

            $response = $this->updateOrCreateTTNDevice($dev_id, $dev_eui, $app_key);
            if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201)
            {
                $device_array['hardware_id']= $dev_id;
                $device_array['key']        = $dev_eui;
                $device_array['app_key']    = $app_key;
            }
            else
            {
                return Response::json(json_decode($response->getBody()), $response->getStatusCode());
            }
        }

        $timeZone  = $request->input('timezone','UTC');
        $result    = $this->updateOrCreateDevice($device_array, $timeZone);

        if (gettype($result) == 'object' && $request->filled('create_ttn_device') && isset($device_array['app_key']))
        {
            $device          = Device::find($result->id);
            $device->app_key = $device_array['app_key'];
            $device->app_eui = strtolower(env('TTN_APP_EUI'));
        }
        else
        {
            $device = $result;
        }

        if (gettype($device) == 'array' && isset($device['http_response_code'])) // error code from TTN
        {
            $http_response_code = $device['http_response_code'];
            unset($device['http_response_code']);
            return Response::json($device, $http_response_code);
        }

        return Response::json($device, $device == null || gettype($device) == 'array' ? 400 : 200);
    }

    /**
    api/devices/multiple POST
    Store/update multiple Devices in an array of Device objects
    @authenticated
    @bodyParam id integer Device id to update. (Required without key and hardware_id)
    @bodyParam key string DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint. (Required without id and hardware_id)
    @bodyParam hardware_id string Hardware id of the device as device name in TTN. (Required without id and key)
    @bodyParam name string Device name
    @bodyParam hive_id integer Hive that the sensor is measuring. Default: null
    @bodyParam type string Category name of the hive type from the Categories table. Default: beep
    @bodyParam last_message_received timestamp Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    @bodyParam firmware_version string Firmware version of the Device
    @bodyParam hardware_version string Hardware version of the Device
    @bodyParam boot_count integer Amount of boots of the Device
    @bodyParam measurement_interval_min float Measurement interval in minutes
    @bodyParam measurement_transmission_ratio float Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    @bodyParam ble_pin string Bleutooth PIN of Device: 6 numbers between 0-9
    @bodyParam battery_voltage float Last measured battery voltage
    @bodyParam next_downlink_message string Hex string to send via downlink at next connection (LoRaWAN port 6)
    @bodyParam last_downlink_result string Result received from BEEP base after downlink message (LoRaWAN port 5)
    */
    public function storeMultiple(Request $request)
    {
        //die(print_r($request->input()));
        $timeZone   = $request->input('timezone','UTC');

        foreach ($request->input() as $device) 
        {
            $result = $this->updateOrCreateDevice($device, $timeZone);

            if ($result == null || gettype($result) == 'array')
            {
                if (gettype($result) == 'array' && isset($result['http_response_code']))
                {
                    $http_response_code = $result['http_response_code'];
                    unset($result['http_response_code']);
                    return Response::json($result, $http_response_code);
                }
                return Response::json($result, 400);
            }
        }
       
        return $this->index($request);
    }

    /**
    api/devices PUT/PATCH
    Update an existing Device
    @authenticated
    @bodyParam id integer Device id to update. (Required without key and hardware_id)
    @bodyParam key string DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint. (Required without id and hardware_id)
    @bodyParam hardware_id string Hardware id of the device as device name in TTN. (Required without id and key)
    @bodyParam name string Name of the sensor
    @bodyParam hive_id integer Hive that the sensor is measuring. Default: null
    @bodyParam type string Category name of the hive type from the Categories table. Default: beep
    @bodyParam delete boolean If true delete the sensor and all it's data in the Influx database
    @bodyParam last_message_received timestamp Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    @bodyParam firmware_version string Firmware version of the Device
    @bodyParam hardware_version string Hardware version of the Device
    @bodyParam boot_count integer Amount of boots of the Device
    @bodyParam measurement_interval_min float Measurement interval in minutes
    @bodyParam measurement_transmission_ratio float Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    @bodyParam ble_pin string Bleutooth PIN of Device: 6 numbers between 0-9
    @bodyParam battery_voltage float Last measured battery voltage
    @bodyParam next_downlink_message string Hex string to send via downlink at next connection (LoRaWAN port 6)
    @bodyParam last_downlink_result string Result received from BEEP base after downlink message (LoRaWAN port 5)
    */
    public function update(Request $request, $id)
    {
        $result     = null;
        $timeZone   = $request->input('timezone','UTC');

        if ($id)
        {
            $device       = $request->input();
            $device['id'] = $id;
            $result       = $this->updateOrCreateDevice($device, $timeZone);
        }

        if (gettype($result) == 'array' && isset($result['http_response_code']))
        {
            $http_response_code = $result['http_response_code'];
            unset($result['http_response_code']);
            return Response::json($result, $http_response_code);
        }

        return Response::json($result, $result == null || gettype($result) == 'array' ? 400 : 200);
    }

    public function updateOrCreateDevice($device, $timeZone)
    {
        $sid = isset($device['id']) ? $device['id'] : null;
        $key = isset($device['key']) ? strtolower($device['key']) : null;
        $hwi = isset($device['hardware_id']) ? strtolower($device['hardware_id']) : null;

        // Get $sid from hardware_id key combination
        if (isset($key) && !isset($id) && isset($hwi))
        {
            $dev = Device::where('hardware_id', $hwi)->where('key', $key)->first();
            if ($dev)
                $sid = $dev->id;
        }

        // user webapp generated key fix for required hw id
        if (isset($key) && !isset($id) && !isset($hwi))
        {
            $hwi = $key;
            $device['hardware_id'] = $device['key'];
        }

        $validator = Validator::make($device, [
            'key'               => ['required_without_all:id,hardware_id','string','min:4',Rule::unique('sensors', 'key')->ignore($sid)->whereNull('deleted_at')],
            'name'              => 'nullable|string',
            'id'                => ['required_without_all:key,hardware_id','integer', Rule::unique('sensors')->ignore($sid)],
            'hardware_id'       => ['required_without_all:key,id','string'],
            'hive_id'           => 'nullable|integer|exists:hives,id',
            'type'              => 'nullable|string|exists:categories,name',
            'delete'            => 'nullable|boolean'
        ]);

        if ($validator->fails())
        {
            return ['errors'=>$validator->errors().' (KEY/DEV EUI: '.$key.', HW ID: '.$hwi.')', 'http_response_code'=>400];
        }
        else
        {
            if ($this->canUserClaimDevice($sid, $key, $hwi, true, 'updateOrCreateDevice') === false)
                return ['errors'=>'Cannot create device: (KEY/DEV EUI: '.$key.', HW ID: '.$hwi.')', 'http_response_code'=>400];

            $valid_data = $validator->validated();
            $device_new = [];
            $device_obj = null;
            $device_id  = null;
            $user       = Auth::user();

            if ($user)
            {
                if (isset($sid))
                    $device_obj = $user->devices()->find($sid);
                else if (isset($device['hardware_id']))
                    $device_obj = $user->devices()->where('hardware_id', $device['hardware_id'])->first();
                else if (isset($hwi))
                    $device_obj = $user->devices()->where('hardware_id', $hwi)->first();
                else if (isset($device['key']))
                    $device_obj = $user->devices()->where('key', $device['key'])->first();
                else if (isset($key))
                    $device_obj = $user->devices()->where('key', $key)->first();
            }

            if ($device_obj != null)
            {
                // delete
                if (isset($valid_data['delete']) && boolval($valid_data['delete']) === true)
                {
                    // try
                    // {
                    //     $client = new \Influx;
                    //     $query  = 'DELETE from "sensors" WHERE "key" = \''.$device_obj->key.'\'';
                    //     $result = $client::query($query);
                    // }
                    // catch(\Exception $e)
                    // {
                    //     return ['errors'=>'Data values of device with key '.$device_obj->key.' cannot be deleted, try again later...', 'http_response_code'=>500];
                    // }
                    $device_obj->delete();
                    return 'device_deleted';
                }

                // edit
                $device_new = $device_obj->toArray();
                $device_id  = $device_obj->id;
            }

            $typename                  = isset($device['type']) ? $device['type'] : 'beep'; 
            $device_new['category_id'] = Category::findCategoryIdByParentAndName('sensor', $typename);

            // $device_new['id'] = $device_id; 
            //die(print_r([$device_obj, $device]));

            // Update devicename if BEEPBASE-[####] and not a new name is being set
            $reset_device_name = false;
            if ($typename == 'beep' && isset($device['key']) && isset($device['app_key']))
            {
                if (!isset($device['name']))
                    $reset_device_name = true;
                else if (isset($device) && isset($device_obj) && $device['name'] == $device_obj['name'] && substr($device_obj['name'], 0, 9) == 'BEEPBASE-')
                    $reset_device_name = true;
            }
                
            if ($reset_device_name)
            {
                $device_new['name'] = 'BEEPBASE-'.strtoupper(substr($device['key'], -4, 4));
            }
            else if (isset($device['name']))
            {
                $device_new['name'] = $device['name']; 
            }

            if (isset($device['key']))
                $device_new['key'] = $device['key'];
            
            if (isset($device['hive_id']))
            {
                $device_change = false;

                if (!isset($device_new['hive_id']))
                    $device_new['hive_id'] = null;

                if (($device['hive_id'] != null && $device_new['hive_id'] == null) || $device['hive_id'] != $device_new['hive_id'])
                    $device_change = true;

                // Create auto inspection
                if ($device_change)
                {
                    // First set inspection because location will be fixed after setting in hive

                    // Inspection items to add 
                    $device_added   = Category::findCategoryByRootParentAndName('hive', 'device', 'id_added', ['system']);
                    $device_removed = Category::findCategoryByRootParentAndName('hive', 'device', 'id_removed', ['system']);

                    if (isset($device_removed) && ($device['hive_id'] == null || ($device['hive_id'] != null && $device_new['hive_id'] != null))) // removed, or hive_id changed
                    {
                        $notes                      = $device_removed->transName().': '.$device_new['name'];
                        $items                      = [];
                        $items[$device_removed->id] = $sid;
                        Inspection::createInspection($items, $device_new['hive_id'], null, $notes, $timeZone); // set inspection to old hive id (from unchanged device object)
                    }

                    if (isset($device_added) && $device['hive_id'] != null) // device added, or changed to new id
                    {
                        $notes                    = $device_added->transName().': '.$device_new['name'];
                        $items                    = [];
                        $items[$device_added->id] = $sid;
                        Inspection::createInspection($items, $device['hive_id'], null, $notes, $timeZone); 
                    }
                }

                // change hive
                $device_new['hive_id'] = $device['hive_id'];
            }
            
            if (isset($device['last_message_received']))
                $device_new['last_message_received'] = $device['last_message_received'];
            
            if (isset($device['hardware_id']))
                $device_new['hardware_id'] = $hwi;
            
            if (isset($device['firmware_version']))
                $device_new['firmware_version'] = $device['firmware_version'];
            
            if (isset($device['hardware_version']))
                $device_new['hardware_version'] = $device['hardware_version'];
            
            if (isset($device['boot_count']))
                $device_new['boot_count'] = $device['boot_count'];
            
            if (isset($device['measurement_interval_min']))
                $device_new['measurement_interval_min'] = $device['measurement_interval_min'];
            
            if (isset($device['measurement_transmission_ratio']))
                $device_new['measurement_transmission_ratio'] = $device['measurement_transmission_ratio'];
            
            if (isset($device['ble_pin']))
                $device_new['ble_pin'] = $device['ble_pin'];
            
            if (isset($device['battery_voltage']))
                $device_new['battery_voltage'] = $device['battery_voltage'];
            
            if (isset($device['next_downlink_message']))
                $device_new['next_downlink_message'] = $device['next_downlink_message'];
            
            if (isset($device['last_downlink_result']))
                $device_new['last_downlink_result'] = $device['last_downlink_result'];
            
            return Auth::user()->devices()->updateOrCreate(['id'=>$device_id], $device_new);
        }

        return null;
    }

    /**
    * api/devices/clocksync POST
    * Trigger a clock synchronization downlink to a device via The Things Network
    * @authenticated
    * @bodyParam key string required The DEV EUI of the device to send the clock sync command to
    * @response {
    *     "status": "Clock sync downlink scheduled",
    *     "device_id": "0123450a18494a83ee",
    *     "scheduled_time": "2024-01-15 14:30:00",
    *     "timestamp": 1705327800
    * }
    */
    public function clocksync(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'key' => 'required|string'
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $key = strtolower($request->input('key'));

        // Find the device by key (DEV EUI)
        // Also include superadmin role in downlink permission checks
        if (Auth::user()->hasRole(['superadmin', 'admin'])) {
            $device = Device::where('key', $key)->first();
        } else {
            $device = Auth::user()->allDevices()->where('key', $key)->first();
        }

        if (!$device) {
            return Response::json(['error' => 'Device not found or access denied'], 404);
        }

        // Check if device has hardware_id (required for TTN)
        if (!$device->hardware_id) {
            return Response::json(['error' => 'Device has no hardware_id configured for TTN'], 422);
        }

        // Calculate the next scheduled transmission time
        $now = time();
        $timezone = 'Europe/Amsterdam'; // CET timezone as specified
        
        // Get device timing information
        $last_message_received = $device->last_message_received ? strtotime($device->last_message_received) : null;
        $measurement_interval_min = $device->measurement_interval_min ?: 15; // Default to 15 minutes if not set
        
        if (!$last_message_received) {
            return Response::json(['error' => 'Device has no last_message_received timestamp'], 422);
        }

        // Calculate next expected message time
        $interval_seconds = $measurement_interval_min * 60;
        $time_since_last = $now - $last_message_received;
        $messages_missed = floor($time_since_last / $interval_seconds);
        
        // Next message time is the last message time plus the next interval
        $next_message_time = $last_message_received + (($messages_missed + 1) * $interval_seconds);
        
        // Add a small buffer (30 seconds) to ensure the device is listening
        $scheduled_downlink_time = $next_message_time + 30;
        
        // Convert to CET timezone
        $cet_time = new \DateTime();
        $cet_time->setTimezone(new \DateTimeZone($timezone));
        $cet_time->setTimestamp($scheduled_downlink_time);
        $cet_timestamp = $cet_time->getTimestamp();

        // Create the payload: A5 + hex timestamp
        $hex_timestamp = dechex($cet_timestamp);
        $payload_hex = 'A5' . $hex_timestamp;
        $payload_base64 = base64_encode(hex2bin($payload_hex));

        // Prepare the downlink data
        $downlink_data = [
            'downlinks' => [
                [
                    'frm_payload' => $payload_base64,
                    'f_port' => 6
                ]
            ]
        ];

        // Send the downlink via TTN
        try {
            $guzzle = new Client();
            $url = env('TTN_API_URL') . '/as/applications/' . env('TTN_APP_NAME') . '/webhooks/' . env('TTN_APP_NAME') . '/devices/' . $device->hardware_id . '/down/push';
            
            $response = $guzzle->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('TTN_API_KEY'),
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'beep-base-test'
                ],
                'json' => $downlink_data
            ]);

            if ($response->getStatusCode() == 202 || $response->getStatusCode() == 200) {
                // Update the device with the scheduled downlink
                $device->next_downlink_message = $payload_hex;
                $device->save();

                return Response::json([
                    'status' => 'Clock sync downlink scheduled',
                    'device_id' => $device->hardware_id,
                    'scheduled_time' => $cet_time->format('Y-m-d H:i:s'),
                    'timestamp' => $cet_timestamp,
                    'payload' => $payload_hex
                ], 200);
            } else {
                return Response::json([
                    'error' => 'Failed to schedule downlink',
                    'status_code' => $response->getStatusCode()
                ], 500);
            }
        } catch (RequestException $e) {
            $error_response = $e->hasResponse() ? json_decode($e->getResponse()->getBody(), true) : null;
            Log::error('TTN downlink failed', [
                'device_id' => $device->hardware_id,
                'error' => $error_response ?: $e->getMessage()
            ]);
            
            return Response::json([
                'error' => 'Failed to send downlink to TTN',
                'details' => $error_response ?: $e->getMessage()
            ], 500);
        }
    }

    /**
    * api/devices/lora_reset POST
    * Send a LoRa reset command to a device
    * @authenticated
    * @bodyParam key string required DEV EUI of the device to reset
    * @response {
    *     "status": "LoRa reset downlink scheduled",
    *     "device_id": "0123450d3707d834ee",
    *     "payload": "940D"
    * }
    */
    public function lora_reset(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'key' => 'required|string'
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $key = strtolower($request->input('key'));

        // Find the device by key (DEV EUI)
        // Also include superadmin role in downlink permission checks
        if (Auth::user()->hasRole(['superadmin', 'admin'])) {
            $device = Device::where('key', $key)->first();
        } else {
            $device = Auth::user()->allDevices()->where('key', $key)->first();
        }

        if (!$device) {
            return Response::json(['error' => 'Device not found or access denied'], 404);
        }

        // Check if device has hardware_id (required for TTN)
        if (!$device->hardware_id) {
            return Response::json(['error' => 'Device has no hardware_id configured for TTN'], 422);
        }

        // Create the payload for LoRa reset
        $payload_hex = '940D';
        $payload_base64 = base64_encode(hex2bin($payload_hex));

        // Prepare the downlink data
        $downlink_data = [
            'downlinks' => [
                [
                    'frm_payload' => $payload_base64,
                    'f_port' => 6
                ]
            ]
        ];

        // Send the downlink via TTN
        try {
            $guzzle = new Client();
            $url = env('TTN_API_URL') . '/as/applications/' . env('TTN_APP_NAME') . '/webhooks/' . env('TTN_APP_NAME') . '/devices/' . $device->hardware_id . '/down/push';
            
            $response = $guzzle->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('TTN_API_KEY'),
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'beep-base-test'
                ],
                'json' => $downlink_data
            ]);

            if ($response->getStatusCode() == 202 || $response->getStatusCode() == 200) {
                // Update the device with the scheduled downlink
                $device->next_downlink_message = $payload_hex;
                $device->save();

                return Response::json([
                    'status' => 'LoRa reset downlink scheduled',
                    'device_id' => $device->hardware_id,
                    'payload' => $payload_hex
                ], 200);
            } else {
                return Response::json([
                    'error' => 'Failed to schedule downlink',
                    'status_code' => $response->getStatusCode()
                ], 500);
            }
        } catch (RequestException $e) {
            $error_response = $e->hasResponse() ? json_decode($e->getResponse()->getBody(), true) : null;
            Log::error('TTN downlink failed', [
                'device_id' => $device->hardware_id,
                'error' => $error_response ?: $e->getMessage()
            ]);
            
            return Response::json([
                'error' => 'Failed to send downlink to TTN',
                'details' => $error_response ?: $e->getMessage()
            ], 500);
        }
    }

    /**
    * api/devices/interval POST
    * Set the measurement interval for a device
    * @authenticated
    * @bodyParam key string required DEV EUI of the device
    * @bodyParam interval integer required Measurement interval in minutes (1-1440)
    * @response {
    *     "status": "Interval downlink scheduled",
    *     "device_id": "0123450d3707d834ee",
    *     "interval_minutes": 15,
    *     "payload": "9D010F"
    * }
    */
    public function interval(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'interval' => 'required|integer|min:1|max:1440'
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        $key = strtolower($request->input('key'));
        $interval = $request->input('interval');

        // Find the device by key (DEV EUI)
        // Also include superadmin role in downlink permission checks
        if (Auth::user()->hasRole(['superadmin', 'admin'])) {
            $device = Device::where('key', $key)->first();
        } else {
            $device = Auth::user()->allDevices()->where('key', $key)->first();
        }

        if (!$device) {
            return Response::json(['error' => 'Device not found or access denied'], 404);
        }

        // Check if device has hardware_id (required for TTN)
        if (!$device->hardware_id) {
            return Response::json(['error' => 'Device has no hardware_id configured for TTN'], 422);
        }

        // Create the payload for interval setting
        // Convert interval to hex (2 bytes, big endian)
        $interval_hex = str_pad(dechex($interval), 4, '0', STR_PAD_LEFT);
        $payload_hex = '9D01' . $interval_hex;
        $payload_base64 = base64_encode(hex2bin($payload_hex));

        // Prepare the downlink data
        $downlink_data = [
            'downlinks' => [
                [
                    'frm_payload' => $payload_base64,
                    'f_port' => 6
                ]
            ]
        ];

        // Send the downlink via TTN
        try {
            $guzzle = new Client();
            $url = env('TTN_API_URL') . '/as/applications/' . env('TTN_APP_NAME') . '/webhooks/' . env('TTN_APP_NAME') . '/devices/' . $device->hardware_id . '/down/push';
            
            $response = $guzzle->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('TTN_API_KEY'),
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'beep-base-test'
                ],
                'json' => $downlink_data
            ]);

            if ($response->getStatusCode() == 202 || $response->getStatusCode() == 200) {
                // Update the device with the scheduled downlink
                $device->next_downlink_message = $payload_hex;
                $device->save();

                return Response::json([
                    'status' => 'Interval downlink scheduled',
                    'device_id' => $device->hardware_id,
                    'interval_minutes' => $interval,
                    'payload' => $payload_hex
                ], 200);
            } else {
                return Response::json([
                    'error' => 'Failed to schedule downlink',
                    'status_code' => $response->getStatusCode()
                ], 500);
            }
        } catch (RequestException $e) {
            $error_response = $e->hasResponse() ? json_decode($e->getResponse()->getBody(), true) : null;
            Log::error('TTN downlink failed', [
                'device_id' => $device->hardware_id,
                'error' => $error_response ?: $e->getMessage()
            ]);
            
            return Response::json([
                'error' => 'Failed to send downlink to TTN',
                'details' => $error_response ?: $e->getMessage()
            ], 500);
        }
    }
}
