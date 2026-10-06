<?php

namespace Tests\Feature\Api;

use App\User;

/**
 * The token guard and the endpoints behind it.
 *
 * config/auth.php defines the 'api' guard with the token driver and
 * 'hash' => false, so the api_token column is sent as a bearer token.
 * Everything else in the API sits behind auth:api + verifiedApi, so a
 * regression here is invisible in the happy path and fatal in practice.
 */
class AuthenticationTest extends ApiTestCase
{
    /** @test */
    public function it_returns_a_token_when_authenticating()
    {
        $response = $this->postJson('/api/authenticate', [], $this->authHeaders());

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('api_token'));
    }

    /** @test */
    public function it_answers_with_the_same_token_that_was_sent()
    {
        $response = $this->postJson('/api/authenticate', [], $this->authHeaders());

        $this->assertSame($this->user->api_token, $response->json('api_token'));
    }

    /** @test */
    public function it_rejects_a_request_without_a_token()
    {
        $response = $this->postJson('/api/authenticate');

        $this->assertContains($response->status(), [401, 403, 400]);
    }

    /** @test */
    public function it_rejects_a_request_with_an_unknown_token()
    {
        $response = $this->postJson('/api/authenticate', [], [
            'Authorization' => 'Bearer not-a-real-token',
            'Accept'        => 'application/json',
        ]);

        $this->assertContains($response->status(), [401, 403, 400]);
    }

    /** @test */
    public function it_refuses_protected_endpoints_without_a_token()
    {
        // /api/devices sits behind auth:api + verifiedApi. Without a token it
        // must not return the (empty) device collection.
        $response = $this->getJson('/api/devices');

        $this->assertNotSame(200, $response->status());
    }

    /** @test */
    public function it_serves_a_protected_endpoint_with_a_valid_token()
    {
        // Devices list answers 404 "no_devices_found" for an authenticated
        // user with no devices, which is how we know the guard let us through
        // rather than the request being rejected.
        $response = $this->getJson('/api/devices', $this->authHeaders());

        $response->assertStatus(404);
        $this->assertSame('no_devices_found', $response->json());
    }

    /** @test */
    public function it_updates_last_login_on_a_successful_login()
    {
        $this->assertNotNull($this->user->fresh()->last_login);
    }

    /** @test */
    public function it_only_exposes_the_token_holding_user_to_itself()
    {
        $mine = $this->postJson('/api/authenticate', [], $this->authHeaders())->json('id');

        $this->assertSame((int) $this->user->id, (int) $mine);
    }

    /** @test */
    public function it_never_returns_the_password_hash()
    {
        $body = $this->postJson('/api/authenticate', [], $this->authHeaders())->json();

        $this->assertArrayNotHasKey('password', $body);
    }

    /** @test */
    public function it_keeps_a_users_devices_scoped_to_that_user()
    {
        // A second account must not see the first one's apiary.
        $this->createApiary();

        $this->registerAndVerify(['email' => 'other@example.com']);
        $token = $this->login(['email' => 'other@example.com']);

        $response = $this->requestAs($token, 'GET', '/api/locations');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('locations'));
    }
}
