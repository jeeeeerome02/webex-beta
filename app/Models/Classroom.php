<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'name',
        'description',
        'theme_color',
        'type',
        'invite_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (Classroom $classroom) {
            if (empty($classroom->invite_token)) {
                $classroom->invite_token = Str::random(32);
            }
        });
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'classroom_members')
            ->withPivot('status')
            ->withTimestamps();
    }
}
