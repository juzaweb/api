<?php

namespace Juzaweb\Modules\Api\Auth;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\Request;
use Juzaweb\Modules\Api\Models\ApiKey;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Guards\TokenGuard;
use Laravel\Passport\PassportUserProvider;
use League\OAuth2\Server\ResourceServer;

class JuzawebApiGuard
{
    public function __construct(
        protected ResourceServer $server,
        protected PassportUserProvider $provider,
        protected ClientRepository $clients,
        protected Encrypter $encrypter
    ) {}

    public function __invoke(Request $request)
    {
        // 1. Check for API Key
        $apiKey = $request->header('x-api-key');
        if ($apiKey) {
            $keyModel = ApiKey::where('key', hash('sha256', $apiKey))->first();

            if ($keyModel && ! $keyModel->revoked) {
                if ($keyModel->expires_at && $keyModel->expires_at->isPast()) {
                    return null;
                }

                $keyModel->update(['last_used_at' => now()]);

                return $keyModel->user;
            }

            return null;
        }

        // 2. Fallback to Passport
        return (new TokenGuard(
            $this->server,
            $this->provider,
            $this->clients,
            $this->encrypter,
            $request
        ))->user($request);
    }
}
