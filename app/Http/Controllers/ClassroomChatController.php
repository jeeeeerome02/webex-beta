<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassroomChatController extends Controller
{
    private function authorize(string $token): array
    {
        $user = Auth::guard('api')->user();
        $classroom = Classroom::where('invite_token', $token)->firstOrFail();
        $isOwner = $classroom->teacher_id === $user->id;
        $isMember = $classroom->members()->where('user_id', $user->id)->wherePivot('status', 'approved')->exists();

        abort_unless($isOwner || $isMember, 403, 'No access to this class.');

        return [$user, $classroom];
    }

    public function index(string $token): JsonResponse
    {
        [$user, $classroom] = $this->authorize($token);

        $messages = $classroom->messages()->with('author')->latest()->take(100)->get()->reverse()->values()
            ->map(fn ($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'author' => $m->author->name,
                'author_id' => $m->user_id,
                'avatar_url' => $m->author->avatar(),
                'is_mine' => $m->user_id === $user->id,
                'time' => $m->created_at->format('M j, g:i A'),
            ]);

        return response()->json(['messages' => $messages]);
    }

    public function store(Request $request, string $token): JsonResponse
    {
        [$user, $classroom] = $this->authorize($token);

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $msg = $classroom->messages()->create(['user_id' => $user->id, 'body' => $data['body']]);
        $msg->load('author');

        return response()->json(['message' => [
            'id' => $msg->id,
            'body' => $msg->body,
            'author' => $msg->author->name,
            'author_id' => $msg->user_id,
            'avatar_url' => $msg->author->avatar(),
            'is_mine' => true,
            'time' => $msg->created_at->format('M j, g:i A'),
        ]], 201);
    }
}
