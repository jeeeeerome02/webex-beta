<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassroomTask extends Model
{
    protected $fillable = [
        'classroom_id',
        'user_id',
        'name',
        'type',
        'description',
        'deadline_type',
        'deadline_at',
        'deadline_end',
        'duration',
        'visibility',
        'visible_members',
        'advanced',
        'questions',
        'archived_at',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'deadline_end' => 'datetime',
        'visible_members' => 'array',
        'advanced' => 'array',
        'questions' => 'array',
        'archived_at' => 'datetime',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ClassroomTaskSubmission::class, 'task_id');
    }
}
