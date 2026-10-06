<?php

namespace Tests\Feature\Api;

use App\Category;
use App\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Device creation, and a defect found while writing these tests.
 *
 * DeviceController::updateOrCreateDevice writes an automatic inspection when
 * a device is attached to a hive. The category it records is looked up with
 * Category::findCategoryByRootParentAndName('hive', 'device', 'id_added'),
 * and that helper returns a new, unsaved Category when the lookup misses:
 *
 *     return $parent->children()->where('name', $name)->first();
 *     // -> first() on a missing row gives null, so the method falls
 *        through to: return new Category;
 *
 * On an install built from migrations plus MeasurementSeeder the lookup
 * misses, because MeasurementSeeder creates no 'device', 'id_added' or
 * 'id_removed' categories, and storage/app/new_taxonomy_tables.sql - the
 * dump the taxonomy migration loads - does not contain them either. So
 * $device_added->id is null and the controller does
 *
 *     $items[$device_added->id] = $sid;   // $items[""] = $sid
 *
 * Inspection::createInspection() then inserts an inspection_items row with
 * an empty category_id, which violates
 * inspection_items_category_id_foreign and surfaces as a 500.
 *
 * The tests below record that behaviour. The device test is expected to
 * fail on an install without those categories; if it starts passing, the
 * seeder has been extended and these notes should be revisited.
 *
 * One naming note: App\Device sets protected $table = 'sensors', so the
 * table assertions below say 'sensors'. There is no 'devices' table.
 */
class DeviceTest extends ApiTestCase
{
    use RefreshDatabase;

    /** @test */
    public function the_taxonomy_lacks_the_categories_the_auto_inspection_needs()
    {
        // 'hive' does exist as the taxonomy root, so only the two levels
        // below it are missing.
        $this->assertNotNull(
            Category::where('name', 'hive')->first(),
            'expected the taxonomy root "hive" to exist'
        );

        foreach (['device', 'id_added', 'id_removed'] as $name) {
            $found = Category::where('name', $name)->first();

            $this->assertNull(
                $found,
                "expected migrations + MeasurementSeeder to contain no '$name' category"
            );
        }
    }

    /** @test */
    public function the_category_lookup_returns_an_unsaved_model_when_it_misses()
    {
        $found = Category::findCategoryByRootParentAndName('hive', 'device', 'id_added');

        $this->assertFalse($found->exists);
        $this->assertNull($found->id);
    }

    /** @test */
    public function attaching_a_device_to_a_hive_answers_a_server_error()
    {
        $apiary = $this->createApiary();
        $hive   = $apiary->hives()->first();

        // The insert of the automatic inspection_items row violates
        // inspection_items_category_id_foreign, and the handler turns that
        // into a 500. Recorded as-is: a 4xx would be the honest answer for a
        // taxonomy that is missing two rows, but changing it is a code fix
        // rather than a test.
        $response = $this->postJson('/api/devices', [
            'name'    => 'BEEP base 1',
            'hive_id' => $hive->id,
            'key'     => 'beepbase0001',
        ], $this->authHeaders());

        $response->assertStatus(500);
    }

    /** @test */
    public function the_failure_underneath_is_a_foreign_key_violation()
    {
        $apiary = $this->createApiary();
        $hive   = $apiary->hives()->first();

        // Same request, with the exception handler taken out of the way so the
        // underlying database error is visible.
        $this->withoutExceptionHandling();

        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->postJson('/api/devices', [
            'name'    => 'BEEP base 1',
            'hive_id' => $hive->id,
            'key'     => 'beepbase0001',
        ], $this->authHeaders());
    }

    /** @test */
    public function it_stores_a_device_that_is_not_attached_to_a_hive()
    {
        // With no hive_id the automatic-inspection branch is skipped and the
        // same request succeeds. That narrows the failure to the hive
        // attachment path rather than to device creation in general.
        $response = $this->postJson('/api/devices', [
            'name' => 'Detached base',
            'key'  => 'detached0001',
        ], $this->authHeaders());

        $response->assertStatus(200);
        $this->assertDatabaseHas('sensors', ['name' => 'Detached base']);
    }

    /** @test */
    public function it_requires_a_key_an_id_or_a_hardware_id()
    {
        $response = $this->postJson('/api/devices', [
            'name' => 'No identity',
            'key'  => '',
        ], $this->authHeaders());

        $response->assertStatus(400);
        $this->assertSame(0, Device::count());
    }

    /** @test */
    public function it_accepts_a_device_without_a_name()
    {
        // name is nullable: the endpoint stores the device with a null name.
        // The Vue app always sends one, so this records the leniency rather
        // than treating it as intended.
        $response = $this->postJson('/api/devices', [
            'key' => 'noname000001',
        ], $this->authHeaders());

        $response->assertStatus(200);
        $this->assertDatabaseHas('sensors', ['key' => 'noname000001']);
    }

    /** @test */
    public function it_answers_with_no_devices_found_for_an_empty_account()
    {
        $response = $this->getJson('/api/devices', $this->authHeaders());

        $response->assertStatus(404);
        $this->assertSame('no_devices_found', $response->json());
    }

    /** @test */
    public function it_lists_devices_that_exist()
    {
        $this->postJson('/api/devices', [
            'name' => 'Detached base',
            'key'  => 'detached0001',
        ], $this->authHeaders())->assertStatus(200);

        $response = $this->getJson('/api/devices', $this->authHeaders());

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json());
    }

    /** @test */
    public function it_hides_devices_from_other_users()
    {
        $this->postJson('/api/devices', [
            'name' => 'Private base',
            'key'  => 'private0001',
        ], $this->authHeaders())->assertStatus(200);

        $this->registerAndVerify(['email' => 'nosy@example.com']);
        $token = $this->login(['email' => 'nosy@example.com']);

        $response = $this->requestAs($token, 'GET', '/api/devices');

        $response->assertStatus(404);
        $this->assertSame('no_devices_found', $response->json());
    }
}
