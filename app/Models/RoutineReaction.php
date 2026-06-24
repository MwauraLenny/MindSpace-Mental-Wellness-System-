<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutineReaction extends Model
{
    public const ALLOWED_REACTIONS = ['heart', 'clap', 'idea', 'spark'];

    public const REACTION_META = [
        'heart' => ['emoji' => '❤️', 'label' => 'Helpful'],
        'clap' => ['emoji' => '👏', 'label' => 'Great'],
        'idea' => ['emoji' => '💡', 'label' => 'Useful idea'],
        'spark' => ['emoji' => '✨', 'label' => 'Inspiring'],
    ];

    protected $fillable = [
        'routine_id',
        'user_id',
        'reaction',
    ];

    public function routine()
    {
        return $this->belongsTo(Routine::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getReactionEmojiAttribute(): string
    {
        return self::REACTION_META[$this->reaction]['emoji'] ?? '🙂';
    }

    public function getReactionLabelAttribute(): string
    {
        return self::REACTION_META[$this->reaction]['label'] ?? 'Reaction';
    }
}
