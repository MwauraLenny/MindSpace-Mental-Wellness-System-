<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    use HasFactory;

    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'session_identifier',
        'ip_address',
        'user_agent',
        'started_at',
        'last_activity_at',
        'ended_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'ended_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function fingerprint(string $sessionId): string
    {
        return hash('sha256', $sessionId);
    }

    public static function trackActivity(
        int $userId,
        string $sessionId,
        ?string $ipAddress,
        ?string $userAgent,
        array $meta = []
    ): void {
        $fingerprint = self::fingerprint($sessionId);
        $now = CarbonImmutable::now();

        $record = self::query()->firstOrNew([
            'session_identifier' => $fingerprint,
        ]);

        if (! $record->exists) {
            $record->started_at = $now;
        }

        $record->user_id = $userId;
        $record->ip_address = $ipAddress;
        $record->user_agent = $userAgent;
        $record->last_activity_at = $now;
        $record->ended_at = null;
        $record->meta = $meta;
        $record->save();
    }

    public static function endBySessionId(string $sessionId): void
    {
        self::query()
            ->where('session_identifier', self::fingerprint($sessionId))
            ->whereNull('ended_at')
            ->update([
                'ended_at' => CarbonImmutable::now(),
            ]);
    }

    public static function endAllForUser(int $userId): void
    {
        self::query()
            ->where('user_id', $userId)
            ->whereNull('ended_at')
            ->update([
                'ended_at' => CarbonImmutable::now(),
            ]);
    }
}
