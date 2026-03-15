<?php

namespace Juzaweb\Modules\Api\Tests\Feature\Api;

use Juzaweb\Modules\Api\Tests\TestCase;
use Juzaweb\Modules\Core\Models\User;
use Juzaweb\Modules\Api\Models\ApiKey;

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
        $providers[] = \Laravel\Passport\PassportServiceProvider::class;
        return $providers;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(\League\OAuth2\Server\ResourceServer::class);
        $this->mock(\Laravel\Passport\ClientRepository::class);
    }

    public function testShowReturnsUserProfile()
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
            ]
        ]);
    }

    public function testShowReturnsUnauthorizedForGuest()
    {
        $response = $this->getJson('api/v1/profile');

        $response->assertStatus(401);
    }
}
