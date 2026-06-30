<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomTaskSubmission extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'answers',
        'logs',
        'status',
        'score',
        'started_at',
        'submitted_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'logs' => 'array',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(ClassroomTask::class, 'task_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
