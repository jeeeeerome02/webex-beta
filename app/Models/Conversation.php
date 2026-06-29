<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['user_one_id', 'user_two_id', 'nick_one', 'nick_two'];

    public function messages()
    {
        return $this->hasMany(DirectMessage::class)->orderBy('created_at');
    }

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function other(int $authId): User
    {
        return $this->user_one_id === $authId ? $this->userTwo : $this->userOne;
    }

    public function nickFor(int $authId): ?string
    {
        return $this->user_one_id === $authId ? $this->nick_one : $this->nick_two;
    }
}
