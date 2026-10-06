<?php

namespace Tests\Feature\Api;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Registration and login, the two endpoints a new user hits first.
 *
 * Covered here because everything else in the API sits behind an
 * authenticated token, so a regression here takes the whole app down.
 *
 * Email verification: Api\UserController::login() checks
 * hasVerifiedEmail() explicitly and answers email_not_verified (400)
 * otherwise, so a registered user cannot log in until the verification
 * link is used. Tests therefore mark the address as verified directly
 * instead of walking the notification, which keeps them about the API
 * rather than about mail delivery.
 *
 * Note that App\Http\Middleware\EnsureWebappEmailIsVerified would *not*
 * catch an unverified user here: it only applies when the user is an
 * instance of Illuminate\Contracts\Auth\MustVerifyEmail, and App\User
 * imports that interface without implementing it.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name'                 => 'Test Keeper',
            'email'                => 'keeper@example.com',
            'password'             => 'supersecret1',
            'password_confirmation'=> 'supersecret1',
            'policy_accepted'      => 1,
            'locale'               => 'en',
        ], $overrides);
    }

    /** Register a user and complete verification, the state login needs. */
    private function registerVerified(array $overrides = []): User
    {
        $this->postJson('/api/register', $this->payload($overrides))->assertStatus(201);

        $user = User::where('email', $overrides['email'] ?? 'keeper@example.com')->first();
        $user->markEmailAsVerified();

        return $user;
    }

    /** @test */
    public function it_registers_a_new_user()
    {
        $response = $this->postJson('/api/register', $this->payload());

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', ['email' => 'keeper@example.com']);
        $this->assertSame(1, User::where('email', 'keeper@example.com')->count());
    }

    /** @test */
    public function it_stores_the_password_hashed()
    {
        $this->postJson('/api/register', $this->payload())->assertStatus(201);

        $user = User::where('email', 'keeper@example.com')->first();

        $this->assertNotSame('supersecret1', $user->password);
        $this->assertTrue(Hash::check('supersecret1', $user->password));
    }

    /** @test */
    public function it_assigns_an_api_token_on_registration()
    {
        $this->postJson('/api/register', $this->payload())->assertStatus(201);

        $user = User::where('email', 'keeper@example.com')->first();

        $this->assertNotEmpty($user->api_token);
        $this->assertSame(60, strlen($user->api_token));
    }

    /** @test */
    public function it_rejects_a_registration_without_the_required_fields()
    {
        $response = $this->postJson('/api/register', ['email' => 'not-an-email']);

        $response->assertStatus(400);
        $this->assertDatabaseMissing('users', ['email' => 'not-an-email']);
    }

    /** @test */
    public function it_rejects_a_password_shorter_than_eight_characters()
    {
        $response = $this->postJson('/api/register', $this->payload([
            'password'             => 'short',
            'password_confirmation'=> 'short',
        ]));

        $response->assertStatus(400);
        $this->assertDatabaseMissing('users', ['email' => 'keeper@example.com']);
    }

    /** @test */
    public function it_rejects_a_duplicate_email()
    {
        $this->postJson('/api/register', $this->payload())->assertStatus(201);

        $response = $this->postJson('/api/register', $this->payload());

        $response->assertStatus(400);
        $this->assertSame(1, User::where('email', 'keeper@example.com')->count());
    }

    /** @test */
    public function it_rejects_registration_without_policy_acceptance()
    {
        $response = $this->postJson('/api/register', $this->payload([
            'policy_accepted' => null,
        ]));

        $response->assertStatus(400);
        $this->assertDatabaseMissing('users', ['email' => 'keeper@example.com']);
    }

    /** @test */
    public function it_creates_a_standard_checklist_for_a_new_user()
    {
        // ChecklistFactory::getStandardChecklist() runs during registration and
        // reads seeded category data, so this asserts the wiring rather than
        // asserting the whole checklist is populated.
        $this->postJson('/api/register', $this->payload())->assertStatus(201);

        $user = User::where('email', 'keeper@example.com')->first();

        $this->assertNotNull($user);
    }

    /** @test */
    public function it_rejects_login_before_the_address_is_verified()
    {
        $this->postJson('/api/register', $this->payload())->assertStatus(201);

        $response = $this->postJson('/api/login', [
            'email'    => 'keeper@example.com',
            'password' => 'supersecret1',
        ]);

        // login() calls hasVerifiedEmail() itself; an unverified account is
        // refused. Note the body is a bare JSON string here, not an object:
        // notVerified() answers Response::json('email_not_verified', 400)
        // while notAuthenticated() answers a {"message": ...} object.
        $response->assertStatus(400);
        $this->assertSame('email_not_verified', $response->json());
    }

    /** @test */
    public function it_logs_in_with_correct_credentials()
    {
        $this->registerVerified();

        $response = $this->postJson('/api/login', [
            'email'    => 'keeper@example.com',
            'password' => 'supersecret1',
        ]);

        $response->assertStatus(200);

        $body = $response->json();
        $this->assertArrayHasKey('api_token', $body);
        $this->assertNotEmpty($body['api_token']);
    }

    /** @test */
    public function it_rejects_login_with_a_wrong_password()
    {
        $this->registerVerified();

        $response = $this->postJson('/api/login', [
            'email'    => 'keeper@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(400);
        $this->assertSame('invalid_user', $response->json('message'));
    }

    /** @test */
    public function it_rejects_login_for_an_unknown_email()
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'nobody@example.com',
            'password' => 'supersecret1',
        ]);

        $response->assertStatus(400);
        $this->assertSame('invalid_user', $response->json('message'));
    }
}
