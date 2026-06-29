<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassroomPostLike extends Model
{
    protected $table = 'classroom_post_likes';

    protected $fillable = ['post_id', 'user_id'];
}
