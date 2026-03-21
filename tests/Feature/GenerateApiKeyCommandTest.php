<?php

namespace Juzaweb\Modules\Api\Tests\Feature;

use Juzaweb\Modules\Api\Tests\TestCase;
use Juzaweb\Modules\Core\Models\User;

class GenerateApiKeyCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__.'/../../vendor/juzaweb/core/database/migrations');
    }

    public function test_can_generate_api_key_for_existing_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $this->artisan('api-key:generate', [
            '--email' => 'test@example.com',
        ])
            ->expectsOutputToContain('API Key generated successfully for user Test User (test@example.com)')
            ->expectsOutputToContain('API Key: ')
            ->assertSuccessful();

        $this->assertDatabaseHas('api_keys', [
            'user_id' => $user->id,
            'user_type' => User::class,
            'name' => 'Default API Key',
        ]);
    }

    public function test_fails_when_user_not_found()
    {
        $this->artisan('api-key:generate', [
            '--email' => 'nonexistent@example.com',
        ])
            ->expectsOutput('User not found.')
            ->assertFailed();
    }
}
