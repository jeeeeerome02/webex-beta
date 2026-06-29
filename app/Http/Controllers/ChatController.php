<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function conversations(): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $list = Conversation::where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->with(['userOne', 'userTwo', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->get()
            ->sortByDesc(fn ($c) => optional($c->messages->first())->created_at ?? $c->created_at)
            ->map(function ($c) use ($user) {
                $other = $c->other($user->id);
                $last = $c->messages->first();

                return [
                    'id' => $c->id,
                    'user_id' => $other->id,
                    'name' => $c->nickFor($user->id) ?: $other->name,
                    'real_name' => $other->name,
                    'avatar_url' => $other->avatar(),
                    'last' => $last?->body,
                    'time' => $last ? $last->created_at->diffForHumans() : '',
                ];
            })->values();

        return response()->json(['conversations' => $list]);
    }

    public function open(int $userId): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $other = User::findOrFail($userId);

        $c = $this->find($user->id, $userId);
        if (! $c) {
            $c = Conversation::create([
                'user_one_id' => min($user->id, $userId),
                'user_two_id' => max($user->id, $userId),
            ]);
        }

        return response()->json([
            'conversation' => [
                'id' => $c->id,
                'user_id' => $other->id,
                'name' => $c->nickFor($user->id) ?: $other->name,
                'real_name' => $other->name,
                'avatar_url' => $other->avatar(),
            ],
            'messages' => $c->messages()->with('author')->get()->map(fn ($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'is_mine' => $m->user_id === $user->id,
                'avatar_url' => $m->author->avatar(),
                'time' => $m->created_at->format('M j, g:i A'),
            ]),
        ]);
    }

    public function send(Request $request, int $id): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $c = Conversation::findOrFail($id);
        abort_unless($c->user_one_id === $user->id || $c->user_two_id === $user->id, 403);

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $m = $c->messages()->create(['user_id' => $user->id, 'body' => $data['body']]);
        $c->touch();

        return response()->json(['message' => [
            'id' => $m->id,
            'body' => $m->body,
            'is_mine' => true,
            'avatar_url' => $user->avatar(),
            'time' => $m->created_at->format('M j, g:i A'),
        ]], 201);
    }

    public function nickname(Request $request, int $id): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $c = Conversation::findOrFail($id);
        abort_unless($c->user_one_id === $user->id || $c->user_two_id === $user->id, 403);

        $data = $request->validate(['nickname' => ['nullable', 'string', 'max:60']]);
        $c->update($c->user_one_id === $user->id ? ['nick_one' => $data['nickname']] : ['nick_two' => $data['nickname']]);

        return response()->json(['message' => 'Nickname updated.']);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $c = Conversation::findOrFail($id);
        abort_unless($c->user_one_id === $user->id || $c->user_two_id === $user->id, 403);
        $c->delete();

        return response()->json(['message' => 'Conversation deleted.']);
    }

    private function find(int $a, int $b): ?Conversation
    {
        return Conversation::where('user_one_id', min($a, $b))->where('user_two_id', max($a, $b))->first();
    }
}
