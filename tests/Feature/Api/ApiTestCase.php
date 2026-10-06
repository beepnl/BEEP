<?php

namespace Tests\Feature\Api;

use App\User;
use Tests\TestCase;
use App\Hive;
use App\Category;
use App\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Shared setup for the API feature tests.
 *
 * Two things every authenticated endpoint needs:
 *
 * - a verified user with an api_token, because the 'api' guard in
 *   config/auth.php uses the token driver and Api\UserController::login()
 *   refuses an unverified address.
 *
 * - seeded taxonomy, because Api\LocationController@store reads
 *   Continent::where('abbr', 'eu') and
 *   Category::findCategoryByParentAndName('location_type', 'fixed')
 *   before it can save anything. DatabaseSeeder does not run
 *   MeasurementSeeder, so the seed is requested explicitly here.
 */
abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    protected $user;

    /** @var string */
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // The seeders carry no namespace, so composer maps them as global
        // classes (see the classmap entry 'MeasurementSeeder').
        $this->seed(\MeasurementSeeder::class);

        $this->registerAndVerify();
        $this->token = $this->login();
    }

    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'Test Keeper',
            'email'                 => 'keeper@example.com',
            'password'              => 'supersecret1',
            'password_confirmation' => 'supersecret1',
            'policy_accepted'       => 1,
            'locale'                => 'en',
        ], $overrides);
    }

    protected function registerAndVerify(array $overrides = []): User
    {
        $this->postJson('/api/register', $this->payload($overrides))->assertStatus(201);

        $this->user = User::where('email', $overrides['email'] ?? 'keeper@example.com')->firstOrFail();
        $this->user->markEmailAsVerified();

        return $this->user;
    }

    protected function login(array $overrides = []): string
    {
        // /api/login calls Auth::attempt(), which only exists on the session
        // guard. A bearer request earlier in the test leaves auth.defaults
        // pointing at 'api', so it is set back here.
        $this->app['auth']->shouldUse('web');

        $response = $this->postJson('/api/login', [
            'email'    => $overrides['email'] ?? 'keeper@example.com',
            'password' => $overrides['password'] ?? 'supersecret1',
        ]);

        $response->assertStatus(200);

        return $response->json('api_token');
    }

    /**
     * Issue a request as the holder of a specific bearer token.
     *
     * A test simulates several HTTP requests against one booted application,
     * but Illuminate\Auth\AuthManager caches the guard objects it builds and
     * TokenGuard holds whichever user it last matched. Production rebuilds
     * the container on every request; a test cannot, so without help here the
     * second request is still answered as the first account - which reads
     * exactly like an authorisation bug and is not one.
     *
     * The Authorization header is sent as well, so the token still travels
     * the normal path; actingAs() only decides which row the guard starts
     * from.
     */
    protected function requestAs(string $token, string $method, string $uri, array $data = [])
    {
        $this->actingAs(
            User::where('api_token', $token)->firstOrFail(),
            'api'
        );

        return $this->json($method, $uri, $data, $this->tokenHeaders($token));
    }

    /** Bearer headers for a token that is not the current $this->token. */
    protected function tokenHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];
    }

    /**
     * Headers for the token guard. config/auth.php sets 'hash' => false for
     * the api guard, so the raw api_token column is the bearer value.
     */
    protected function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ];
    }

    /**
     * Resolve a seeded category id by name.
     *
     * The numeric ids are an artefact of the order MeasurementSeeder inserts
     * categories in, so tests look the category up by name rather than
     * hardcoding a number that a future seeder change would move.
     */
    protected function categoryId(string $name): int
    {
        $id = Category::where('name', $name)->value('id');

        $this->assertNotNull($id, "expected the seeder to provide a '$name' category");

        return (int) $id;
    }

    /**
     * Create an apiary through the API and return the stored model.
     *
     * LocationController@store ends with return $this->show(...), so a
     * successful create answers 200 with the location body, not 201.
     */
    protected function createApiary(string $name = 'Home Apiary'): Location
    {
        $response = $this->postJson('/api/locations', ['name' => $name], $this->authHeaders());
        $response->assertStatus(200);

        return Location::where('name', $name)->firstOrFail();
    }

    /**
     * Create a hive inside $apiary through the API.
     *
     * Api\HiveController@store requires the layer composition: either a
     * `layers` array, or `brood_layers` plus `honey_layers`. The Vue app
     * always sends one of the two, so the helper sends the two-number form.
     * hive_type_id is left out because the controller falls back to 63.
     */
    protected function createHive(Location $apiary, string $name = 'Hive 1'): Hive
    {
        $response = $this->postJson('/api/hives', [
            'name'         => $name,
            'location_id'  => $apiary->id,
            'brood_layers' => 2,
            'honey_layers' => 1,
        ], $this->authHeaders());

        $response->assertStatus(200);

        return Hive::where('name', $name)->firstOrFail();
    }
}
