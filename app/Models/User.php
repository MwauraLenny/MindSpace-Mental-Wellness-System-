<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'anonymous_sharing',
        'suspended_at',
        'suspension_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'anonymous_sharing' => 'boolean',
            'suspended_at' => 'datetime',
        ];
    }

    public function getIsSuspendedAttribute(): bool
    {
        return $this->suspended_at !== null;
    }

    public function journals()
    {
        return $this->hasMany(Journal::class);
    }

    public function roleRecord()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function moodEntries()
    {
        return $this->hasMany(MoodEntry::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function recommendationHistory()
    {
        return $this->hasMany(RecommendationHistory::class);
    }

    public function userSessions()
    {
        return $this->hasMany(UserSession::class);
    }
}
