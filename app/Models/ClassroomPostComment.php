<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassroomPostComment extends Model
{
    protected $fillable = ['post_id', 'parent_id', 'user_id', 'body'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ClassroomPostComment::class, 'parent_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ClassroomCommentLike::class, 'comment_id');
    }
}
