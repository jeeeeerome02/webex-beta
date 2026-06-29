<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassroomPost extends Model
{
    protected $fillable = ['classroom_id', 'user_id', 'body', 'comments_enabled', 'is_hidden'];

    protected $casts = [
        'comments_enabled' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ClassroomPostComment::class, 'post_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ClassroomPostLike::class, 'post_id');
    }
}
