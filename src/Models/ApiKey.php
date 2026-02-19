<?php

namespace Juzaweb\Modules\Api\Models;

use Juzaweb\Modules\Core\Models\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $table = 'jw_api_keys';

    protected $fillable = [
        'user_id',
        'user_type',
        'name',
        'key',
        'scopes',
        'revoked',
        'expires_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'revoked' => 'boolean',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    public static function generateKey(): string
    {
        return Str::random(64);
    }
}
