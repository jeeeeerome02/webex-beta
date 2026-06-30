<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['user_one_id', 'user_two_id', 'nick_one', 'nick_two', 'read_one_at', 'read_two_at'];

    protected $casts = [
        'read_one_at' => 'datetime',
        'read_two_at' => 'datetime',
    ];

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

    public function readAtFor(int $authId)
    {
        return $this->user_one_id === $authId ? $this->read_one_at : $this->read_two_at;
    }

    public function markReadFor(int $authId): void
    {
        $this->update($this->user_one_id === $authId ? ['read_one_at' => now()] : ['read_two_at' => now()]);
    }

    public function unreadFor(int $authId): int
    {
        $readAt = $this->readAtFor($authId);

        return $this->messages()
            ->where('user_id', '!=', $authId)
            ->when($readAt, fn ($q) => $q->where('created_at', '>', $readAt))
            ->count();
    }
}
