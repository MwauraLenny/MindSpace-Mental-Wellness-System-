<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Journal extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'entry_date',
        'mood_log_id',
        'visibility',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moodLog()
    {
        return $this->belongsTo(MoodLog::class, 'mood_log_id');
    }

    public function getBodyAttribute(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            // Keep legacy plaintext entries readable while new writes stay encrypted.
            return $value;
        }
    }

    public function setBodyAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['body'] = $value;

            return;
        }

        $this->attributes['body'] = Crypt::encryptString((string) $value);
    }
}
