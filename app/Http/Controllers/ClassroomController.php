<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClassroomController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $owned = $user->ownedClassrooms()
            ->withCount('members')
            ->latest()
            ->get()
            ->map(fn ($c) => $this->serialize($c, true));

        $joined = $user->classrooms()
            ->wherePivot('status', 'approved')
            ->where('teacher_id', '!=', $user->id)
            ->withCount('members')
            ->get()
            ->map(fn ($c) => $this->serialize($c, false));

        return response()->json([
            'owned' => $owned,
            'joined' => $joined,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        if (! $user->isTeacher()) {
            return response()->json(['message' => 'Only teachers can create classes.'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'theme_color' => ['required', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'type' => ['required', Rule::in(['public', 'private'])],
        ]);

        $classroom = $user->ownedClassrooms()->create($validated);
        $classroom->loadCount('members');

        return response()->json(['classroom' => $this->serialize($classroom, true)], 201);
    }

    public function showByToken(string $token): JsonResponse
    {
        $classroom = Classroom::with('teacher')->where('invite_token', $token)->firstOrFail();
        $user = Auth::guard('api')->user();

        $membership = null;
        if ($user) {
            $member = $classroom->members()->where('user_id', $user->id)->first();
            $membership = $member?->pivot->status ?? ($classroom->teacher_id === $user->id ? 'owner' : null);
        }

        return response()->json([
            'classroom' => [
                'name' => $classroom->name,
                'description' => $classroom->description,
                'theme_color' => $classroom->theme_color,
                'type' => $classroom->type,
                'invite_token' => $classroom->invite_token,
                'teacher' => $classroom->teacher->name,
            ],
            'membership' => $membership,
        ]);
    }

    public function join(string $token): JsonResponse
    {
        $classroom = Classroom::where('invite_token', $token)->firstOrFail();
        $user = Auth::guard('api')->user();

        if ($classroom->teacher_id === $user->id) {
            return response()->json(['message' => 'You own this class.', 'status' => 'owner']);
        }

        if ($classroom->members()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages(['class' => ['You already joined or requested this class.']]);
        }

        $status = $classroom->type === 'public' ? 'approved' : 'pending';
        $classroom->members()->attach($user->id, ['status' => $status]);

        return response()->json([
            'message' => $status === 'approved' ? 'Joined successfully.' : 'Join request sent. Awaiting approval.',
            'status' => $status,
        ]);
    }

    private function serialize(Classroom $c, bool $owner): array
    {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'description' => $c->description,
            'theme_color' => $c->theme_color,
            'type' => $c->type,
            'invite_token' => $c->invite_token,
            'members_count' => $c->members_count ?? 0,
            'is_owner' => $owner,
        ];
    }
}
