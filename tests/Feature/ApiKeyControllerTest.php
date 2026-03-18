<?php

namespace Juzaweb\Modules\Api\Tests\Feature;

use Juzaweb\Modules\Api\Models\ApiKey;
use Juzaweb\Modules\Api\Tests\TestCase;
use Juzaweb\Modules\Core\Models\User;

/**
 * @OA\Exclude()
 */
class ApiKeyControllerTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.guards.admin', [
            'driver' => 'session',
            'provider' => 'users',
        ]);
    }

    public function test_store_api_key_creates_record_in_api_keys_table()
    {
        // 1. Create a user
        $user = User::factory()->create(['is_super_admin' => 1]);
        $this->actingAs($user, 'admin');

        // 2. Call the store endpoint
        $response = $this->postJson(route('admin.api.keys.store'), [
            'name' => 'My Test Key',
        ]);

        // 3. Assert response is successful
        $response->assertStatus(200);

        // 4. Assert data is in api_keys table
        $this->assertDatabaseHas('api_keys', [
            'name' => 'My Test Key',
            'user_id' => $user->id,
            'user_type' => get_class($user),
        ]);

        // 5. Assert the response contains the token
        $json = $response->json();
        $this->assertTrue(isset($json['token']) || isset($json['data']['token']), 'Response should contain token');

        $token = $json['data']['token'] ?? $json['token'];
        $apiKey = ApiKey::where('name', 'My Test Key')->first();

        // 6. Assert key is hashed
        $this->assertNotEquals($token, $apiKey->key);
        $this->assertEquals(hash('sha256', $token), $apiKey->key);
    }

    public function test_index_lists_api_keys_from_table()
    {
        $user = User::factory()->create(['is_super_admin' => 1]);
        $this->actingAs($user, 'admin');

        // Create a key in the api_keys table manually
        $apiKey = new ApiKey;
        $apiKey->fill([
            'name' => 'Existing Key',
        ]);
        $apiKey->user_id = $user->id;
        $apiKey->user_type = get_class($user);
        $apiKey->key = 'some-random-key';
        $apiKey->save();

        // Call index
        $response = $this->get(route('admin.api.keys.index'));

        $response->assertStatus(200);

        // Assert the table exists in the view
        $response->assertSee('id="jw-datatable"', false);
    }

    public function test_destroy_removes_api_key_from_table()
    {
        $user = User::factory()->create(['is_super_admin' => 1]);
        $this->actingAs($user, 'admin');

        $apiKey = new ApiKey;
        $apiKey->fill([
            'name' => 'Key to Delete',
        ]);
        $apiKey->user_id = $user->id;
        $apiKey->user_type = get_class($user);
        $apiKey->key = 'delete-me';
        $apiKey->save();

        $response = $this->deleteJson(route('admin.api.keys.destroy', ['id' => $apiKey->id]));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('api_keys', [
            'id' => $apiKey->id,
        ]);
    }
}
