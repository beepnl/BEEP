<?php

use App\Device;
use App\Http\Controllers\Api\DeviceController;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Request;

require_once __DIR__.'/../TestCase.php';

class HeliumProvisioningTest extends TestCase
{
    private $heliumEnvKeys = [
        'HELIUM_APP_EUI',
        'HELIUM_PROVISION_DRIVER',
        'HELIUM_CHIRPSTACK_REST_URL',
        'HELIUM_CHIRPSTACK_API_TOKEN',
        'HELIUM_CHIRPSTACK_APPLICATION_ID',
        'HELIUM_CHIRPSTACK_DEVICE_PROFILE_ID',
        'HELIUM_CHIRPSTACK_CONFIGURE_HTTP',
        'HELIUM_CHIRPSTACK_HTTP_EVENT_URL',
        'HELIUM_CONSOLE_API_KEY',
        'HELIUM_PROVISION_URL',
        'HELIUM_COVERAGE_PROVISION_URL',
    ];

    protected function tearDown(): void
    {
        foreach ($this->heliumEnvKeys as $key)
            $this->unsetEnv($key);

        parent::tearDown();
    }

    public function test_chirpstack_rest_provisioning_creates_device_and_keys(): void
    {
        $this->setEnv('HELIUM_CHIRPSTACK_REST_URL', 'https://openlns.example');
        $this->setEnv('HELIUM_CHIRPSTACK_API_TOKEN', 'test-token');
        $this->setEnv('HELIUM_CHIRPSTACK_APPLICATION_ID', 'application-id');
        $this->setEnv('HELIUM_CHIRPSTACK_DEVICE_PROFILE_ID', 'device-profile-id');

        $controller = new TestHeliumDeviceController([
            new GuzzleResponse(204),
            new GuzzleResponse(201, [], '{"id":"device-created"}'),
            new GuzzleResponse(201, [], '{"id":"keys-created"}'),
        ]);

        $device = new Device([
            'name' => 'BEEPBASE-ABCD',
            'hardware_id' => '0123456789abcdef',
        ]);
        $device->id = 123;

        $response = $this->invokePrivate(
            $controller,
            'provisionHeliumWithChirpStack',
            [
                'helium-123-0123456789abcdef',
                '0011223344556677',
                '70b3d57ed002ee76',
                '00112233445566778899aabbccddeeff',
                $device,
                'device',
            ]
        );

        $this->assertSame(201, $response->getStatusCode());

        $history = $controller->getHistory();
        $this->assertCount(3, $history);

        $this->assertSame('DELETE', $history[0]['request']->getMethod());
        $this->assertSame('https://openlns.example/api/devices/0011223344556677', (string) $history[0]['request']->getUri());

        $this->assertSame('POST', $history[1]['request']->getMethod());
        $this->assertSame('https://openlns.example/api/devices', (string) $history[1]['request']->getUri());
        $this->assertSame('Bearer test-token', $history[1]['request']->getHeaderLine('Authorization'));
        $deviceBody = json_decode((string) $history[1]['request']->getBody(), true);
        $this->assertSame('0011223344556677', $deviceBody['device']['devEui']);
        $this->assertSame('70b3d57ed002ee76', $deviceBody['device']['joinEui']);
        $this->assertSame('application-id', $deviceBody['device']['applicationId']);
        $this->assertSame('device-profile-id', $deviceBody['device']['deviceProfileId']);
        $this->assertSame('123', $deviceBody['device']['tags']['beep_device_id']);
        $this->assertSame('0123456789abcdef', $deviceBody['device']['tags']['beep_hardware_id']);
        $this->assertSame('device', $deviceBody['device']['tags']['purpose']);
        $this->assertSame('helium', $deviceBody['device']['tags']['provider']);

        $this->assertSame('POST', $history[2]['request']->getMethod());
        $this->assertSame('https://openlns.example/api/devices/0011223344556677/keys', (string) $history[2]['request']->getUri());
        $keysBody = json_decode((string) $history[2]['request']->getBody(), true);
        $this->assertSame('0011223344556677', $keysBody['deviceKeys']['devEui']);
        $this->assertSame('00112233445566778899aabbccddeeff', $keysBody['deviceKeys']['nwkKey']);
    }

    public function test_chirpstack_rest_can_configure_http_integration(): void
    {
        $this->setEnv('HELIUM_CHIRPSTACK_REST_URL', 'https://openlns.example');
        $this->setEnv('HELIUM_CHIRPSTACK_API_TOKEN', 'test-token');
        $this->setEnv('HELIUM_CHIRPSTACK_APPLICATION_ID', 'application-id');
        $this->setEnv('HELIUM_CHIRPSTACK_DEVICE_PROFILE_ID', 'device-profile-id');
        $this->setEnv('HELIUM_CHIRPSTACK_CONFIGURE_HTTP', 'true');
        $this->setEnv('HELIUM_CHIRPSTACK_HTTP_EVENT_URL', 'https://api.beep.nl/api/lora_sensors');

        $controller = new TestHeliumDeviceController([
            new GuzzleResponse(200, [], '{"ok":true}'),
            new GuzzleResponse(204),
            new GuzzleResponse(201, [], '{"id":"device-created"}'),
            new GuzzleResponse(201, [], '{"id":"keys-created"}'),
        ]);

        $device = new Device([
            'name' => 'BEEPBASE-ABCD',
            'hardware_id' => '0123456789abcdef',
        ]);
        $device->id = 123;

        $response = $this->invokePrivate(
            $controller,
            'provisionHeliumWithChirpStack',
            [
                'helium-123-0123456789abcdef',
                '0011223344556677',
                '70b3d57ed002ee76',
                '00112233445566778899aabbccddeeff',
                $device,
                'device',
            ]
        );

        $this->assertSame(201, $response->getStatusCode());

        $history = $controller->getHistory();
        $this->assertCount(4, $history);
        $this->assertSame('PUT', $history[0]['request']->getMethod());
        $this->assertSame('https://openlns.example/api/applications/application-id/integrations/http', (string) $history[0]['request']->getUri());

        $integrationBody = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame('application-id', $integrationBody['integration']['applicationId']);
        $this->assertSame('JSON', $integrationBody['integration']['encoding']);
        $this->assertSame('https://api.beep.nl/api/lora_sensors', $integrationBody['integration']['eventEndpointUrl']);
    }

    public function test_provider_capabilities_report_chirpstack_configuration(): void
    {
        $this->setEnv('HELIUM_CHIRPSTACK_REST_URL', 'https://openlns.example');
        $this->setEnv('HELIUM_CHIRPSTACK_API_TOKEN', 'test-token');
        $this->setEnv('HELIUM_CHIRPSTACK_APPLICATION_ID', 'application-id');
        $this->setEnv('HELIUM_CHIRPSTACK_DEVICE_PROFILE_ID', 'device-profile-id');

        $controller = new TestHeliumDeviceController();
        $response = $controller->lorawanProviders(Request::create('/api/devices/lorawan/providers', 'GET'));
        $data = json_decode($response->getContent(), true);

        $this->assertTrue($data['helium']['can_provision']);
        $this->assertTrue($data['helium']['can_coverage_check']);
        $this->assertSame('chirpstack_rest', $data['helium']['driver']);
        $this->assertSame([], $data['helium']['missing_config']);
    }

    public function test_provider_capabilities_allow_coverage_only_webhook(): void
    {
        $this->setEnv('HELIUM_COVERAGE_PROVISION_URL', 'https://provisioner.example/coverage');

        $controller = new TestHeliumDeviceController();
        $response = $controller->lorawanProviders(Request::create('/api/devices/lorawan/providers', 'GET'));
        $data = json_decode($response->getContent(), true);

        $this->assertFalse($data['helium']['can_provision']);
        $this->assertTrue($data['helium']['can_coverage_check']);
        $this->assertSame('webhook', $data['helium']['coverage_driver']);
        $this->assertSame([], $data['helium']['missing_coverage_config']);
    }

    public function test_console_check_fails_cleanly_when_not_configured(): void
    {
        $this->artisan('lorawan:helium-check')
            ->expectsOutput('Helium/OpenLNS provisioning status')
            ->expectsOutput('driver: not_configured')
            ->expectsOutput('can_provision: no')
            ->expectsOutput('can_coverage_check: no')
            ->assertExitCode(1);
    }

    public function test_console_check_succeeds_when_chirpstack_is_configured(): void
    {
        $this->setEnv('HELIUM_CHIRPSTACK_REST_URL', 'https://openlns.example');
        $this->setEnv('HELIUM_CHIRPSTACK_API_TOKEN', 'test-token');
        $this->setEnv('HELIUM_CHIRPSTACK_APPLICATION_ID', 'application-id');
        $this->setEnv('HELIUM_CHIRPSTACK_DEVICE_PROFILE_ID', 'device-profile-id');

        $this->artisan('lorawan:helium-check')
            ->expectsOutput('Helium/OpenLNS provisioning status')
            ->expectsOutput('driver: chirpstack_rest')
            ->expectsOutput('can_provision: yes')
            ->expectsOutput('can_coverage_check: yes')
            ->assertExitCode(0);
    }

    private function invokePrivate($object, string $method, array $args = [])
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $args);
    }

    private function setEnv(string $key, string $value): void
    {
        putenv($key.'='.$value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    private function unsetEnv(string $key): void
    {
        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);
    }
}

class TestHeliumDeviceController extends DeviceController
{
    private $history = [];
    private $mockHandler;

    public function __construct(array $responses = [])
    {
        $this->mockHandler = new MockHandler($responses);
    }

    public function getHistory(): array
    {
        return $this->history;
    }

    protected function makeHeliumHttpClient()
    {
        $stack = HandlerStack::create($this->mockHandler);
        $stack->push(Middleware::history($this->history));

        return new Client(['handler' => $stack]);
    }
}
