<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

class MoodLog extends Model
{
    public $timestamps = false;

    public const CATEGORIES = [
        'happy' => ['label' => 'Happy', 'emoji' => '😄', 'score' => 5],
        'sad' => ['label' => 'Sad', 'emoji' => '😢', 'score' => 1],
        'angry' => ['label' => 'Angry', 'emoji' => '😠', 'score' => 2],
        'stressed' => ['label' => 'Stressed', 'emoji' => '😫', 'score' => 2],
        'anxious' => ['label' => 'Anxious', 'emoji' => '😰', 'score' => 3],
        'relaxed' => ['label' => 'Relaxed', 'emoji' => '😌', 'score' => 4],
        'excited' => ['label' => 'Excited', 'emoji' => '🤩', 'score' => 5],
        'tired' => ['label' => 'Tired', 'emoji' => '😴', 'score' => 3],
    ];

    private const SCORE_FALLBACK_CATEGORY = [
        1 => 'sad',
        2 => 'stressed',
        3 => 'anxious',
        4 => 'relaxed',
        5 => 'happy',
    ];

    protected $fillable = [
        'user_id',
        'mood_category',
        'mood_value',
        'journal_note',
        'routine_shared',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
        ];
    }

    public static function categories(): array
    {
        return self::CATEGORIES;
    }

    public static function categoriesByScoreDesc(): array
    {
        $categories = self::CATEGORIES;

        uasort($categories, static function (array $left, array $right): int {
            $scoreComparison = $right['score'] <=> $left['score'];

            if ($scoreComparison !== 0) {
                return $scoreComparison;
            }

            return strcmp($left['label'], $right['label']);
        });

        return $categories;
    }

    public static function categoryMeta(string $key): array
    {
        if (! isset(self::CATEGORIES[$key])) {
            throw new InvalidArgumentException('Invalid mood category key provided.');
        }

        return self::CATEGORIES[$key];
    }

    public static function scoreFromCategory(string $key): int
    {
        return self::categoryMeta($key)['score'];
    }

    public function getMoodCategoryKeyAttribute(): string
    {
        if (! empty($this->attributes['mood_category']) && isset(self::CATEGORIES[$this->attributes['mood_category']])) {
            return $this->attributes['mood_category'];
        }

        return self::SCORE_FALLBACK_CATEGORY[$this->mood_value] ?? 'tired';
    }

    public function getMoodMetaAttribute(): array
    {
        return self::CATEGORIES[$this->mood_category_key];
    }

    public function getMoodLabelAttribute(): string
    {
        return $this->mood_meta['label'];
    }

    public function getMoodEmojiAttribute(): string
    {
        return $this->mood_meta['emoji'];
    }

    public function getMoodScoreAttribute(): int
    {
        return (int) $this->mood_value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function journals()
    {
        return $this->hasMany(Journal::class, 'mood_log_id');
    }

    public function recommendationHistory()
    {
        return $this->hasMany(RecommendationHistory::class, 'mood_log_id');
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

        if (app()->environment('testing')) {
            $this->attributes['journal_note'] = (string) $value;

            return;
        }

        $this->attributes['journal_note'] = Crypt::encryptString((string) $value);
    }
}
