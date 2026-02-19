<?php

namespace Juzaweb\Modules\Api\Tests\Feature;

use Juzaweb\Modules\Api\Models\ApiKey;
use Juzaweb\Modules\Api\Tests\TestCase;
use Juzaweb\Modules\Core\Models\User;

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

    protected function getPackageAliases($app): array
    {
        $aliases = parent::getPackageAliases($app);
        $aliases['Menu'] = \Juzaweb\Modules\Core\Facades\Menu::class;
        return $aliases;
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../../vendor/juzaweb/core/database/migrations');

        $this->artisan('migrate', ['--database' => config('database.default')])->run();
    }

    public function test_it_can_create_api_key_via_api()
    {
        $user = new User();
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->password = 'password';
        $user->email_verified_at = now();
        $user->is_super_admin = 1;
        $user->save();

        $this->actingAs($user, 'admin');

        $response = $this->postJson(route('admin.api.keys.store'), [
            'name' => 'My Key',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);

        $this->assertDatabaseHas('jw_api_keys', [
            'name' => 'My Key',
            'user_id' => $user->id,
        ]);
    }

    public function test_it_can_delete_api_key_via_api()
    {
        $user = new User();
        $user->name = 'Test User 2';
        $user->email = 'test2@example.com';
        $user->password = 'password';
        $user->email_verified_at = now();
        $user->is_super_admin = 1;
        $user->save();

        $this->actingAs($user, 'admin');

        $keyString = ApiKey::generateKey();
        $apiKey = new ApiKey();
        $apiKey->fill([
            'name' => 'Key to Delete',
            'scopes' => [],
            'revoked' => false,
        ]);
        $apiKey->key = $keyString;
        $apiKey->user_id = $user->id;
        $apiKey->user_type = get_class($user);
        $apiKey->save();

        $response = $this->delete(route('admin.api.keys.destroy', ['id' => $apiKey->id]));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('jw_api_keys', [
            'id' => $apiKey->id,
        ]);
    }

    public function test_it_can_list_api_keys()
    {
        $user = new User();
        $user->name = 'Test User List';
        $user->email = 'testlist@example.com';
        $user->password = 'password';
        $user->email_verified_at = now();
        $user->is_super_admin = 1;
        $user->save();

        $this->actingAs($user, 'admin');

        $keyString = ApiKey::generateKey();
        $apiKey = new ApiKey();
        $apiKey->fill([
            'name' => 'Key to List',
            'scopes' => [],
            'revoked' => false,
        ]);
        $apiKey->key = $keyString;
        $apiKey->user_id = $user->id;
        $apiKey->user_type = get_class($user);
        $apiKey->save();

        $response = $this->get(route('admin.api.keys.index'));

        $response->assertStatus(200);
    }

    public function test_it_cannot_delete_other_users_api_key()
    {
        $user1 = new User();
        $user1->name = 'User 1';
        $user1->email = 'user1@example.com';
        $user1->password = 'password';
        $user1->email_verified_at = now();
        $user1->is_super_admin = 1;
        $user1->save();

        $user2 = new User();
        $user2->name = 'User 2';
        $user2->email = 'user2@example.com';
        $user2->password = 'password';
        $user2->email_verified_at = now();
        $user2->is_super_admin = 1;
        $user2->save();

        $keyString = ApiKey::generateKey();
        $apiKey = new ApiKey();
        $apiKey->fill([
            'name' => 'User 1 Key',
            'scopes' => [],
            'revoked' => false,
        ]);
        $apiKey->key = $keyString;
        $apiKey->user_id = $user1->id;
        $apiKey->user_type = get_class($user1);
        $apiKey->save();

        $this->actingAs($user2, 'admin');

        $response = $this->delete(route('admin.api.keys.destroy', ['id' => $apiKey->id]));

        $response->assertStatus(200);

        $this->assertDatabaseHas('jw_api_keys', [
            'id' => $apiKey->id,
        ]);
    }
}
