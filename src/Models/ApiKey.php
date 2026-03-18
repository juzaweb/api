<?php

namespace Juzaweb\Modules\Api\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Juzaweb\Modules\Core\Models\Model;

class ApiKey extends Model
{
    public ?string $plain_text_key = null;

    protected $table = 'api_keys';

    protected $fillable = [
        'user_id',
        'user_type',
        'name',
        'key',
        'scopes',
        'revoked',
        'expires_at',
        'last_used_at',
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

    protected static function booted(): void
    {
        static::creating(function (ApiKey $model) {
            $plainTextKey = $model->key ?: Str::random(64);
            $model->plain_text_key = $plainTextKey;
            $model->key = hash('sha256', $plainTextKey);
        });
    }

    public static function generateKey(): string
    {
        return Str::random(64);
    }
}
