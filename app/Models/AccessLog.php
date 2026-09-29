<?php

namespace App\Models;

use Database\Factories\AccessLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'visitor_uuid', 'session_id', 'ip_address', 'method', 'url', 'route_name', 'query_string', 'referer', 'user_agent', 'browser', 'browser_version', 'operating_system', 'device', 'device_type', 'language', 'status_code', 'duration_ms', 'is_authenticated'])]
class AccessLog extends Model
{
    /** @use HasFactory<AccessLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_authenticated' => 'boolean',
            'status_code' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
