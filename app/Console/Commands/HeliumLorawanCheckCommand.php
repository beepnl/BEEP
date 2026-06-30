<?php

namespace App\Console\Commands;

use App\Device;
use App\Http\Controllers\Api\DeviceController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use ReflectionMethod;

class HeliumLorawanCheckCommand extends Command
{
    protected $signature = 'lorawan:helium-check
        {--live : Create/update a disposable test device in the configured Helium/OpenLNS account}
        {--force : Do not ask for confirmation when --live is used}
        {--purpose=device : Provisioning purpose to test: device or coverage_check}
        {--dev-eui= : Optional 16 hex DevEUI for live test}
        {--app-eui= : Optional 16 hex AppEUI/JoinEUI for live test; HELIUM_APP_EUI is used when omitted}
        {--app-key= : Optional 32 hex AppKey/NwkKey for live test}
        {--hardware-id=helium-check : Synthetic hardware id used for the live disposable test device}
        {--name=BEEP Helium provisioning check : Synthetic device name used for the live disposable test device}';

    protected $description = 'Check whether the BEEP API server is configured for automated Helium/OpenLNS LoRaWAN provisioning';

    public function handle()
    {
        $controller = app(DeviceController::class);
        $status = json_decode(
            $controller->lorawanProviders(Request::create('/api/devices/lorawan/providers', 'GET'))->getContent(),
            true
        );

        $helium = isset($status['helium']) && is_array($status['helium']) ? $status['helium'] : [];
        $this->line('Helium/OpenLNS provisioning status');
        $this->line('driver: '.($helium['driver'] ?? 'unknown'));
        $this->line('coverage_driver: '.($helium['coverage_driver'] ?? 'unknown'));
        $this->line('can_provision: '.$this->yesNo($helium['can_provision'] ?? false));
        $this->line('can_coverage_check: '.$this->yesNo($helium['can_coverage_check'] ?? false));
        $this->line('missing_config: '.$this->formatList($helium['missing_config'] ?? []));
        $this->line('missing_coverage_config: '.$this->formatList($helium['missing_coverage_config'] ?? []));

        if (!$this->option('live'))
            return ($helium['can_provision'] ?? false) || ($helium['can_coverage_check'] ?? false) ? 0 : 1;

        $purpose = (string) $this->option('purpose');
        if (!in_array($purpose, ['device', 'coverage_check'], true))
        {
            $this->error('Invalid --purpose. Use device or coverage_check.');
            return 1;
        }

        $capability = $purpose == 'coverage_check' ? 'can_coverage_check' : 'can_provision';
        if (empty($helium[$capability]))
        {
            $this->error('Helium/OpenLNS is not configured for '.$purpose.'.');
            return 1;
        }

        if (!$this->option('force') && !$this->confirm('This will create/update a disposable device in the configured Helium/OpenLNS account. Continue?', false))
            return 1;

        $devEui = strtolower((string) ($this->option('dev-eui') ?: bin2hex(random_bytes(8))));
        $appEui = strtolower((string) ($this->option('app-eui') ?: env('HELIUM_APP_EUI') ?: bin2hex(random_bytes(8))));
        $appKey = strtolower((string) ($this->option('app-key') ?: bin2hex(random_bytes(16))));

        if (!preg_match('/^[0-9a-f]{16}$/', $devEui) || !preg_match('/^[0-9a-f]{16}$/', $appEui) || !preg_match('/^[0-9a-f]{32}$/', $appKey))
        {
            $this->error('DevEUI/AppEUI/AppKey must be valid lowercase or uppercase hexadecimal values.');
            return 1;
        }

        $device = new Device([
            'name' => (string) $this->option('name'),
            'hardware_id' => (string) $this->option('hardware-id'),
        ]);
        $device->id = 0;

        $networkDeviceId = 'helium-check-'.$device->hardware_id;
        $response = $this->invokeController($controller, 'provisionHeliumDevice', [
            $networkDeviceId,
            $devEui,
            $appEui,
            $appKey,
            $device,
            $purpose,
        ]);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300)
        {
            $this->error('Live Helium/OpenLNS provisioning failed with HTTP '.$response->getStatusCode().'.');
            $this->line($this->responseBody($response));
            return 1;
        }

        $this->info('Live Helium/OpenLNS provisioning succeeded.');
        $this->line('network_device_id: '.$networkDeviceId);
        $this->line('dev_eui: '.$devEui);
        $this->line('app_eui: '.$appEui);
        $this->line('app_key: '.$appKey);

        return 0;
    }

    private function yesNo($value)
    {
        return $value ? 'yes' : 'no';
    }

    private function formatList($items)
    {
        if (!is_array($items) || count($items) == 0)
            return '-';

        return implode(', ', $items);
    }

    private function invokeController(DeviceController $controller, string $method, array $args)
    {
        $reflection = new ReflectionMethod($controller, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($controller, $args);
    }

    private function responseBody($response)
    {
        if (method_exists($response, 'getBody'))
            return (string) $response->getBody();

        if (method_exists($response, 'getContent'))
            return $response->getContent();

        return '';
    }
}
