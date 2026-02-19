<?php

namespace Juzaweb\Modules\Api\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Juzaweb\Modules\Api\Models\ApiKey;
use Juzaweb\Modules\Api\Providers\ApiServiceProvider;
use Juzaweb\Modules\Core\Models\User;
use Orchestra\Testbench\TestCase;

class ApiKeyTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Juzaweb\QueryCache\QueryCacheServiceProvider::class,
            \Juzaweb\Hooks\HooksServiceProvider::class,
            \Juzaweb\Modules\Core\Providers\CoreServiceProvider::class,
            ApiServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Theme' => \Juzaweb\Modules\Core\Facades\Theme::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('status')->default('active');
            $table->boolean('is_super_admin')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /** @test */
    public function it_can_create_api_key()
    {
        $user = new User();
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->password = 'password';
        $user->save();

        $keyString = ApiKey::generateKey();

        $apiKey = ApiKey::create([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'name' => 'My API Key',
            'scopes' => ['read'],
            'key' => $keyString,
            'revoked' => false,
            'expires_at' => now()->addDays(30),
        ]);

        $this->assertDatabaseHas('api_keys', [
            'id' => $apiKey->id,
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'key' => $keyString,
            'revoked' => 0,
        ]);

        $this->assertEquals(['read'], $apiKey->scopes);
        $this->assertFalse($apiKey->revoked);
        $this->assertNotNull($apiKey->expires_at);
        $this->assertTrue($apiKey->expires_at->isFuture());
    }
}
