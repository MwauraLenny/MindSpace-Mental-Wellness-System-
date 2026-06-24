<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutineCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function routines()
    {
        return $this->hasMany(Routine::class);
    }
}
