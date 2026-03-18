<?php

namespace Juzaweb\Modules\Api\Tests\Feature\API;

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
}
