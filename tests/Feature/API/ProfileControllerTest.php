<?php

namespace Juzaweb\Modules\Api\Tests\Feature\API;

use Illuminate\Support\Facades\Hash;
use Juzaweb\Modules\Api\Models\ApiKey;
use Juzaweb\Modules\Api\Tests\TestCase;
use Juzaweb\Modules\Core\Models\User;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\PassportServiceProvider;
use League\OAuth2\Server\ResourceServer;

class ProfileControllerTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.guards.api', [
            'driver' => 'juzaweb',
            'provider' => 'users',
        ]);

        $app['config']->set('auth.guards.juzaweb', [
            'driver' => 'juzaweb',
            'provider' => 'users',
        ]);
    }

    protected function getPackageProviders($app): array
    {
        $providers = parent::getPackageProviders($app);
        $providers[] = PassportServiceProvider::class;

        return $providers;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(ResourceServer::class);
        $this->mock(ClientRepository::class);
    }

    public function test_show_returns_user_profile()
    {
        $user = User::factory()->create();

        ApiKey::create([
            'key' => 'test-key',
            'user_id' => $user->id,
            'user_type' => User::class,
            'name' => 'Test Key',
        ]);

        $response = $this->withHeader('x-api-key', 'test-key')
            ->getJson('api/v1/profile');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ]);
    }

    public function test_show_returns_unauthorized_for_guest()
    {
        $response = $this->getJson('api/v1/profile');

        $response->assertStatus(401);
    }

    public function test_update_profile_success()
    {
        $user = User::factory()->create();

        ApiKey::create([
            'key' => 'test-key',
            'user_id' => $user->id,
            'user_type' => User::class,
            'name' => 'Test Key',
        ]);

        $response = $this->withHeader('x-api-key', 'test-key')
            ->putJson('api/v1/profile', [
                'name' => 'New Name',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => 'New Name',
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
        ]);
    }

    public function test_update_password_success()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        ApiKey::create([
            'key' => 'test-key',
            'user_id' => $user->id,
            'user_type' => User::class,
            'name' => 'Test Key',
        ]);

        $response = $this->withHeader('x-api-key', 'test-key')
            ->putJson('api/v1/profile/password', [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertStatus(200);

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_update_password_fails_if_current_password_wrong()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        ApiKey::create([
            'key' => 'test-key',
            'user_id' => $user->id,
            'user_type' => User::class,
            'name' => 'Test Key',
        ]);

        $response = $this->withHeader('x-api-key', 'test-key')
            ->putJson('api/v1/profile/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['current_password']);
    }

    public function test_update_password_fails_if_password_not_confirmed()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        ApiKey::create([
            'key' => 'test-key',
            'user_id' => $user->id,
            'user_type' => User::class,
            'name' => 'Test Key',
        ]);

        $response = $this->withHeader('x-api-key', 'test-key')
            ->putJson('api/v1/profile/password', [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'wrong-confirmation',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }
}
