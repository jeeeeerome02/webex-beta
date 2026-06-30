<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'name',
        'description',
        'course_type',
        'theme_color',
        'type',
        'invite_token',
        'join_approval',
        'leave_approval',
        'allow_posts',
        'show_on_profile',
        'cover_image',
    ];

    protected $casts = [
        'join_approval' => 'boolean',
        'leave_approval' => 'boolean',
        'allow_posts' => 'boolean',
        'show_on_profile' => 'boolean',
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
            ->withPivot('status', 'is_co_teacher', 'show_on_profile')
            ->withTimestamps();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ClassroomPost::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(ClassroomModule::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ClassroomTask::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ClassroomMessage::class);
    }
}
