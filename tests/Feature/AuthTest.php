<?php

namespace Juzaweb\Modules\Api\Tests\Feature;

use Juzaweb\Modules\Api\Tests\TestCase;
use Juzaweb\Modules\Api\Models\ApiKey;
use Laravel\Passport\Passport;
use Laravel\Passport\Client;
use Illuminate\Support\Facades\Route;
use Juzaweb\Modules\Core\Models\User;
use Laravel\Passport\HasApiTokens;

class TestUser extends User
{
    use HasApiTokens;
    protected $table = 'users';
}

class TestClient extends Client
{
    protected $table = 'oauth_clients';

    protected $fillable = [
        'owner_id',
        'owner_type',
        'name',
        'secret',
        'provider',
        'redirect_uris',
        'grant_types',
        'revoked',
    ];

}

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('passport:keys');

        Passport::useClientModel(TestClient::class);

        $client = TestClient::create([
            'owner_id' => null,
            'owner_type' => null,
            'name' => 'Test Personal Client',
            'secret' => 'secret',
            'provider' => 'users',
            'redirect_uris' => ['http://localhost'],
            'grant_types' => ['client_credentials', 'personal_access'],
            'revoked' => false,
        ]);

        // Define a test route protected by 'juzaweb' guard
        Route::middleware('auth:juzaweb')->get('/api/test-auth', function () {
            return response()->json(['message' => 'Authenticated', 'user_id' => auth()->id()]);
        });
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('auth.providers.users.model', TestUser::class);
        $app['config']->set('auth.guards.api', [
            'driver' => 'passport',
            'provider' => 'users',
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            \Laravel\Passport\PassportServiceProvider::class,
        ]);
    }

    public function test_authentication_with_valid_api_key()
    {
        // Create user
        $user = TestUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create API Key
        $apiKey = ApiKey::create([
            'name' => 'Test Key',
            'key' => 'test_api_key_12345',
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'revoked' => false,
        ]);

        $response = $this->withHeaders([
            'x-api-key' => 'test_api_key_12345',
            'Accept' => 'application/json',
        ])->get('/api/test-auth');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Authenticated', 'user_id' => $user->id]);

        // Verify last_used_at updated
        $this->assertNotNull($apiKey->fresh()->last_used_at);
    }

    public function test_authentication_with_invalid_api_key()
    {
        $response = $this->withHeaders([
            'x-api-key' => 'invalid_key',
            'Accept' => 'application/json',
        ])->get('/api/test-auth');

        $response->assertStatus(401);
    }

    public function test_authentication_with_revoked_api_key()
    {
        $user = TestUser::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
        ]);

        ApiKey::create([
            'name' => 'Revoked Key',
            'key' => 'revoked_key',
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'revoked' => true,
        ]);

        $response = $this->withHeaders([
            'x-api-key' => 'revoked_key',
            'Accept' => 'application/json',
        ])->get('/api/test-auth');

        $response->assertStatus(401);
    }

    public function test_authentication_with_passport_token()
    {
        $user = TestUser::create([
            'name' => 'Passport User',
            'email' => 'passport@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create Token manually
        $token = $user->createToken('Test Token')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get('/api/test-auth');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Authenticated', 'user_id' => $user->id]);
    }
    public function test_api_key_relationship()
    {
        $user = TestUser::create([
            'name' => 'Rel User',
            'email' => 'rel@example.com',
            'password' => bcrypt('password'),
        ]);

        $apiKey = ApiKey::create([
            'name' => 'Rel Key',
            'key' => 'rel_key',
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'revoked' => false,
        ]);

        $this->assertNotNull($apiKey->user);
        $this->assertTrue($apiKey->user->is($user));
    }

    public function test_authentication_fallback_precedence()
    {
        $user = TestUser::create([
            'name' => 'Both User',
            'email' => 'both@example.com',
            'password' => bcrypt('password'),
        ]);

        $token = $user->createToken('Both Token')->accessToken;

        // Invalid API Key + Valid Token -> Should fail (401) because API Key is processed first
        $response = $this->withHeaders([
            'x-api-key' => 'invalid_key',
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get('/api/test-auth');

        $response->assertStatus(401);

        // Valid API Key + Valid Token -> Should use API Key user
        $user2 = TestUser::create([
            'name' => 'Key User',
            'email' => 'key@example.com',
            'password' => bcrypt('password'),
        ]);

        ApiKey::create([
            'name' => 'Valid Key',
            'key' => 'valid_key_both',
            'user_id' => $user2->id,
            'user_type' => get_class($user2),
            'revoked' => false,
        ]);

        $response2 = $this->withHeaders([
            'x-api-key' => 'valid_key_both',
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get('/api/test-auth');

        $response2->assertStatus(200)
            ->assertJson(['user_id' => $user2->id]);
    }
}
