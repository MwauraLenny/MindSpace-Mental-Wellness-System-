<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'actor_id',
        'action',
        'target_type',
        'target_id',
        'meta',
        'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'performed_at' => 'datetime',
        ];
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
