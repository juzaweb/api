<?php

namespace Juzaweb\Modules\Api\Tests\Feature\API;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Juzaweb\Modules\Api\Models\ApiKey;
use Juzaweb\Modules\Api\Tests\TestCase;
use Juzaweb\Modules\Core\Models\User;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\PassportServiceProvider;
use League\OAuth2\Server\ResourceServer;

class NotificationControllerTest extends TestCase
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

    public function test_index_returns_user_notifications()
    {
        $user = User::factory()->create();

        ApiKey::create([
            'key' => 'test-key',
            'user_id' => $user->id,
            'user_type' => User::class,
            'name' => 'Test Key',
        ]);

        DatabaseNotification::create([
            'id' => Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Test Notification Data'],
            'read_at' => null,
        ]);

        $response = $this->withHeader('x-api-key', 'test-key')
            ->getJson('api/v1/notifications');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'data',
                    'read_at',
                    'read',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ]);
    }

    public function test_index_returns_unauthorized_for_guest()
    {
        $response = $this->getJson('api/v1/notifications');

        $response->assertStatus(401);
    }
}
