<?php

namespace Tests\Feature\Api;

use App\User;
use App\Language;

/**
 * The per-user locale endpoint.
 *
 * This is the switch the Vue app calls when a user picks a language, so it
 * matters for translation work. It validates min:2|max:2 and nothing else,
 * which means a locale can be stored before the languages table has a row
 * for it. That is the current state of Turkish: Vue offers it, the API has
 * no translations for it, and the UI falls back to English.
 */
class UserLocaleTest extends ApiTestCase
{
    /** @test */
    public function it_stores_a_new_user_locale()
    {
        $response = $this->patchJson('/api/userlocale', [
            'locale' => 'nl',
        ], $this->authHeaders());

        $response->assertStatus(200);
        $this->assertSame('nl', $this->user->fresh()->locale);
    }

    /** @test */
    public function it_overwrites_an_existing_locale()
    {
        $this->patchJson('/api/userlocale', ['locale' => 'nl'], $this->authHeaders())
            ->assertStatus(200);

        $this->patchJson('/api/userlocale', ['locale' => 'de'], $this->authHeaders())
            ->assertStatus(200);

        $this->assertSame('de', $this->user->fresh()->locale);
    }

    /** @test */
    public function it_accepts_a_locale_with_no_row_in_the_languages_table()
    {
        // The endpoint does not consult the languages table, so 'tr' is
        // accepted even though the taxonomy migration - which does insert a
        // handful of languages - has no row for it.
        $response = $this->patchJson('/api/userlocale', [
            'locale' => 'tr',
        ], $this->authHeaders());

        $response->assertStatus(200);
        $this->assertSame('tr', $this->user->fresh()->locale);
        $this->assertSame(0, Language::where('twochar', 'tr')->count());
    }

    /** @test */
    public function it_rejects_a_locale_shorter_than_two_characters()
    {
        $response = $this->patchJson('/api/userlocale', [
            'locale' => 't',
        ], $this->authHeaders());

        $this->assertNotSame(200, $response->status());
        $this->assertNotSame('t', $this->user->fresh()->locale);
    }

    /** @test */
    public function it_rejects_a_locale_longer_than_two_characters()
    {
        $response = $this->patchJson('/api/userlocale', [
            'locale' => 'tur',
        ], $this->authHeaders());

        $this->assertNotSame(200, $response->status());
    }

    /** @test */
    public function it_rejects_a_missing_locale()
    {
        $response = $this->patchJson('/api/userlocale', [], $this->authHeaders());

        $this->assertNotSame(200, $response->status());
    }

    /** @test */
    public function it_requires_a_token()
    {
        $response = $this->patchJson('/api/userlocale', ['locale' => 'nl']);

        $this->assertNotSame(200, $response->status());
    }

    /** @test */
    public function it_changes_only_the_locale_of_the_calling_user()
    {
        $this->patchJson('/api/userlocale', ['locale' => 'nl'], $this->authHeaders())
            ->assertStatus(200);

        $other = User::where('email', 'keeper@example.com')->first();
        $this->assertSame('nl', $other->fresh()->locale);
    }
}
