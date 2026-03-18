<?php

namespace Juzaweb\Modules\Api\Tests\Feature\Auth;

use Illuminate\Support\Facades\Route;
use Juzaweb\Modules\Api\Models\ApiKey;
use Juzaweb\Modules\Api\Tests\TestCase;
use Juzaweb\Modules\Core\Models\User;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\PassportServiceProvider;
use League\OAuth2\Server\ResourceServer;

class JuzawebApiGuardTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

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

        // Define a route for testing
        Route::middleware('auth:juzaweb')->get('/test-auth', function () {
            return response()->json(['message' => 'Authenticated', 'user_id' => auth()->id()]);
        });
    }

    public function test_authentication_with_valid_api_key()
    {
        $user = User::factory()->create();

        // Ensure user is created
        $this->assertNotNull($user);

        ApiKey::create([
            'key' => 'test-key',
            'user_id' => $user->id,
            'user_type' => User::class,
            'name' => 'Test Key',
        ]);

        $response = $this->withHeader('x-api-key', 'test-key')
            ->getJson('/test-auth');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Authenticated', 'user_id' => $user->id]);
    }

    public function test_authentication_fails_with_invalid_api_key()
    {
        $response = $this->withHeader('x-api-key', 'invalid-key')
            ->getJson('/test-auth');

        $response->assertStatus(401);
    }

    public function test_authentication_fails_with_expired_api_key()
    {
        $user = User::factory()->create();

        ApiKey::create([
            'key' => 'expired-key',
            'user_id' => $user->id,
            'user_type' => User::class,
            'name' => 'Expired Key',
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->withHeader('x-api-key', 'expired-key')
            ->getJson('/test-auth');

        $response->assertStatus(401);
    }

    public function test_authentication_fails_with_revoked_api_key()
    {
        $user = User::factory()->create();

        ApiKey::create([
            'key' => 'revoked-key',
            'user_id' => $user->id,
            'user_type' => User::class,
            'name' => 'Revoked Key',
            'revoked' => true,
        ]);

        $response = $this->withHeader('x-api-key', 'revoked-key')
            ->getJson('/test-auth');

        $response->assertStatus(401);
    }
}
