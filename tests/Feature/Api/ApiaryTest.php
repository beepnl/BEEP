<?php

namespace Tests\Feature\Api;

use App\Hive;
use App\Inspection;
use App\Location;

/**
 * The apiary -> hive -> inspection chain.
 *
 * Api\LocationController@store creates the apiary and a configurable number
 * of hives in one call, so these also cover the hive defaults the Vue app
 * relies on (hive_type_id 63 = "custom", colour #FABB13).
 *
 * Note on status codes: none of the create endpoints answer 201. Each ends by
 * delegating to its own show(), so creating an apiary answers 200 with the
 * location body and creating a hive answers 200 with the hive collection.
 * These tests assert what the API does rather than what REST would suggest,
 * so a future change to 201 shows up as a failing test.
 */
class ApiaryTest extends ApiTestCase
{
    /** @test */
    public function it_creates_an_apiary()
    {
        $response = $this->postJson('/api/locations', [
            'name' => 'Home Apiary',
        ], $this->authHeaders());

        $response->assertStatus(200);

        $this->assertDatabaseHas('locations', [
            'name'    => 'Home Apiary',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_returns_the_created_apiary_in_the_body()
    {
        $response = $this->postJson('/api/locations', [
            'name' => 'Echoed Apiary',
        ], $this->authHeaders());

        $response->assertStatus(200);
        $this->assertStringContainsString('Echoed Apiary', $response->getContent());
    }

    /** @test */
    public function it_creates_one_hive_along_with_the_apiary()
    {
        $apiary = $this->createApiary();

        $this->assertSame(1, $apiary->hives()->count());
        $this->assertSame($apiary->id, $apiary->hives()->first()->location_id);
    }

    /** @test */
    public function it_creates_the_requested_number_of_hives()
    {
        $this->postJson('/api/locations', [
            'name'        => 'Riverside',
            'hive_amount' => 4,
        ], $this->authHeaders())->assertStatus(200);

        $this->assertSame(4, Location::where('name', 'Riverside')->firstOrFail()->hives()->count());
    }

    /** @test */
    public function it_assigns_the_default_hive_type_and_colour()
    {
        $hive = $this->createApiary()->hives()->first();

        // LocationController falls back to hive_type_id 63 and #FABB13.
        $this->assertSame('#FABB13', $hive->color);
        $this->assertSame(63, (int) $hive->hive_type_id);
    }

    /** @test */
    public function it_numbers_hives_from_the_requested_offset()
    {
        $this->postJson('/api/locations', [
            'name'        => 'Numbered',
            'hive_amount' => 2,
            'offset'      => 7,
        ], $this->authHeaders())->assertStatus(200);

        // The offset becomes part of the hive name rather than the order
        // column, which stays null.
        $names = Location::where('name', 'Numbered')->firstOrFail()
            ->hives()->orderBy('id')->pluck('name')->all();

        $this->assertSame(['Numbered 7', 'Numbered 8'], $names);
    }

    /** @test */
    public function it_leaves_the_hive_order_column_empty()
    {
        // Recorded because issue #136 asks for hive_order to be stored on the
        // apiary; today the number only reaches the name.
        $hive = $this->createApiary()->hives()->first();

        $this->assertNull($hive->order);
    }

    /** @test */
    public function it_rejects_an_apiary_without_a_name()
    {
        $response = $this->postJson('/api/locations', [], $this->authHeaders());

        $response->assertStatus(422);
        $this->assertSame(0, Location::count());
    }

    /** @test */
    public function it_lists_the_apiaries_of_the_signed_in_user()
    {
        $this->createApiary('First');
        $this->createApiary('Second');

        $response = $this->getJson('/api/locations', $this->authHeaders());

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('locations'));
    }

    /** @test */
    public function it_rejects_an_unknown_hive_type_id()
    {
        $response = $this->postJson('/api/locations', [
            'name'         => 'Bad type',
            'hive_type_id' => 999999,
        ], $this->authHeaders());

        $response->assertStatus(422);
        $this->assertDatabaseMissing('locations', ['name' => 'Bad type']);
    }

    /** @test */
    public function it_stores_coordinates_when_given()
    {
        $this->postJson('/api/locations', [
            'name' => 'Mapped',
            'lat'  => 52.3702157,
            'lon'  => 4.8951679,
        ], $this->authHeaders())->assertStatus(200);

        $apiary = Location::where('name', 'Mapped')->firstOrFail();

        $this->assertSame(52.37, (float) $apiary->coordinate_lat);
        $this->assertSame(4.895, (float) $apiary->coordinate_lon);
    }

    /** @test */
    public function it_hides_apiaries_from_other_users()
    {
        $this->createApiary('Private Apiary');

        $this->registerAndVerify(['email' => 'stranger@example.com']);
        $token = $this->login(['email' => 'stranger@example.com']);

        $response = $this->requestAs($token, 'GET', '/api/locations');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('locations'));
    }

    /** @test */
    public function it_creates_a_hive_in_an_existing_apiary()
    {
        $hive = $this->createHive($this->createApiary(), 'Second Hive');

        $this->assertSame('Second Hive', $hive->name);
    }

    /** @test */
    public function it_requires_the_layer_composition_when_creating_a_hive()
    {
        // HiveController needs either a layers array or brood_layers plus
        // honey_layers. Without them it answers 422.
        $apiary = $this->createApiary();

        $response = $this->postJson('/api/hives', [
            'name'        => 'No layers',
            'location_id' => $apiary->id,
        ], $this->authHeaders());

        $response->assertStatus(422);
        $this->assertSame(1, Hive::count());
    }

    /** @test */
    public function it_records_an_inspection_against_a_hive()
    {
        $hive = $this->createApiary()->hives()->first();

        $response = $this->postJson('/api/inspections/store', [
            'hive_id'    => $hive->id,
            'date'       => '2026-10-01 10:00:00',
            'impression' => 8,
            'attention'  => 2,
            'notes'      => 'Strong brood pattern.',
            'items'      => [$this->categoryId('brood') => 5],
        ], $this->authHeaders());

        $response->assertStatus(201);

        // The inspections table has no hive_id column; a hive is attached
        // through the inspection_hive pivot, so the link is asserted there.
        $inspection = Inspection::firstOrFail();

        $this->assertSame(8, (int) $inspection->impression);
        $this->assertDatabaseHas('inspection_hive', [
            'inspection_id' => $inspection->id,
            'hive_id'       => $hive->id,
        ]);
        $this->assertDatabaseHas('inspection_items', [
            'category_id' => $this->categoryId('brood'),
            'value'       => 5,
        ]);
    }

    /** @test */
    public function it_returns_the_new_inspection_id()
    {
        $hive = $this->createApiary()->hives()->first();

        $response = $this->postJson('/api/inspections/store', [
            'hive_id' => $hive->id,
            'date'    => '2026-10-01 10:00:00',
            'items'   => [$this->categoryId('brood') => 5],
        ], $this->authHeaders());

        $response->assertStatus(201);
        $this->assertSame(
            \App\Inspection::first()->id,
            $response->json(),
            'the endpoint answers with the bare id of the new inspection'
        );
    }

    /** @test */
    public function it_answers_a_server_error_when_an_inspection_carries_no_items()
    {
        // The controller only enters its write path when `items` (or
        // item_ids + item_vals) is filled. Without one, no inspection object
        // is built, control reaches the tail of the method and it answers
        // response()->json('error', 500).
        //
        // A missing `items` is a caller error, so 422 would be the
        // consistent answer here; 500 puts it in the same bucket as a crash.
        // Recorded as-is because the Vue app always sends items.
        $hive = $this->createApiary()->hives()->first();

        $response = $this->postJson('/api/inspections/store', [
            'hive_id' => $hive->id,
            'date'    => '2026-10-01 10:00:00',
            'notes'   => 'No checklist answers here.',
        ], $this->authHeaders());

        $response->assertStatus(500);
        $this->assertSame('error', $response->json());
        $this->assertSame(0, Inspection::count());
    }

    /** @test */
    public function it_rejects_an_inspection_without_a_date()
    {
        $hive = $this->createApiary()->hives()->first();

        $response = $this->postJson('/api/inspections/store', [
            'hive_id' => $hive->id,
            'items'   => [$this->categoryId('brood') => 5],
        ], $this->authHeaders());

        $response->assertStatus(422);
        $this->assertSame(0, Inspection::count());
    }

    /** @test */
    public function it_rejects_an_inspection_for_a_hive_that_does_not_exist()
    {
        $response = $this->postJson('/api/inspections/store', [
            'hive_id' => 999999,
            'date'    => '2026-10-01 10:00:00',
            'items'   => [$this->categoryId('brood') => 5],
        ], $this->authHeaders());

        $response->assertStatus(422);
        $this->assertSame(0, Inspection::count());
    }

    /** @test */
    public function it_accepts_one_inspection_for_several_hives_at_once()
    {
        $apiary = $this->createApiary('Bulk Apiary');
        $this->createHive($apiary, 'A');
        $this->createHive($apiary, 'B');

        $hiveIds = $apiary->hives()->pluck('id')->all();

        $response = $this->postJson('/api/inspections/store', [
            'hive_ids' => $hiveIds,
            'date'     => '2026-10-01 10:00:00',
            'notes'    => 'Bulk round',
            'items'    => [$this->categoryId('brood') => 5],
        ], $this->authHeaders());

        $response->assertStatus(201);
        $this->assertSame(count($hiveIds), Inspection::count());
    }

    /** @test */
    public function it_does_not_record_an_inspection_on_another_users_hive()
    {
        $hiveId = $this->createApiary()->hives()->first()->id;

        $this->registerAndVerify(['email' => 'thief@example.com']);
        $token = $this->login(['email' => 'thief@example.com']);

        $response = $this->requestAs($token, 'POST', '/api/inspections/store', [
            'hive_id' => $hiveId,
            'date'    => '2026-10-01 10:00:00',
            'items'   => [$this->categoryId('brood') => 5],
        ]);

        // The ownership check works: nothing is written. The status is the
        // same 'error' 500 the missing-items path gives, because the
        // controller simply skips a hive it cannot resolve.
        $this->assertSame(0, $this->inspectionsForHive($hiveId));
    }

    /** Count the inspections linked to a hive through the pivot table. */
    protected function inspectionsForHive(int $hiveId): int
    {
        return \Illuminate\Support\Facades\DB::table('inspection_hive')
            ->where('hive_id', $hiveId)
            ->count();
    }
}
