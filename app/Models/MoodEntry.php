<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class MoodEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mood_category',
        'mood_value',
        'journal_note',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getJournalNoteAttribute(?string $value): ?string
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

    public function setJournalNoteAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['journal_note'] = $value;

            return;
        }

        $this->attributes['journal_note'] = Crypt::encryptString((string) $value);
    }
}
