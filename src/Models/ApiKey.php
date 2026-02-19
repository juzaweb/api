<?php

namespace Juzaweb\Modules\Api\Models;

use Juzaweb\Modules\Core\Models\Model;
use Juzaweb\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $table = 'jw_api_keys';

    protected $fillable = [
        'user_id',
        'name',
        'key',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public static function generateKey(): string
    {
        return Str::random(64);
    }
}
