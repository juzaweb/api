<?php

namespace Juzaweb\Modules\Api\Commands;

use Illuminate\Console\Command;
use Juzaweb\Modules\Api\Models\ApiKey;
use Juzaweb\Modules\Core\Models\User;
use Symfony\Component\Console\Input\InputOption;

class GenerateApiKeyCommand extends Command
{
    protected $name = 'api-key:generate';

    protected $description = 'Generate API Key for user';

    public function handle(): int
    {
        $email = $this->option('email') ?? $this->ask('User Email?');

        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        $apiKey = ApiKey::create([
            'user_id' => $user->id,
            'user_type' => User::class,
            'name' => 'Default API Key',
            'scopes' => [],
        ]);

        $this->info("API Key generated successfully for user {$user->name} ({$user->email})");
        $this->info("API Key: {$apiKey->plain_text_key}");

        return self::SUCCESS;
    }

    protected function getOptions(): array
    {
        return [
            ['email', null, InputOption::VALUE_OPTIONAL, 'The email of the user.'],
        ];
    }
}
