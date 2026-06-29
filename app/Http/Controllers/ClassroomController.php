<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\Classroom;
use App\Models\User;
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
            ->whereNull('archived_at')
            ->withCount('members')
            ->latest()
            ->get()
            ->map(fn ($c) => $this->serialize($c, true));

        $joined = $user->classrooms()
            ->wherePivot('status', 'approved')
            ->where('teacher_id', '!=', $user->id)
            ->whereNull('archived_at')
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
            'course_type' => ['nullable', 'string', 'max:255'],
            'theme_color' => ['required', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'type' => ['required', Rule::in(['public', 'private'])],
        ]);

        $isPrivate = $validated['type'] === 'private';
        $validated['join_approval'] = $isPrivate;
        $validated['leave_approval'] = $isPrivate;
        $validated['allow_posts'] = true;

        $classroom = $user->ownedClassrooms()->create($validated);
        $classroom->loadCount('members');

        return response()->json(['classroom' => $this->serialize($classroom, true)], 201);
    }

    public function show(string $token): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $classroom = Classroom::with('teacher')->withCount('members')->where('invite_token', $token)->firstOrFail();

        $isOwner = $classroom->teacher_id === $user->id;
        $isMember = $classroom->members()->where('user_id', $user->id)->wherePivot('status', 'approved')->exists();

        if (! $isOwner && ! $isMember) {
            return response()->json(['message' => 'You do not have access to this class.'], 403);
        }

        $members = $classroom->members()
            ->withPivot('status', 'is_co_teacher')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'email' => $m->email,
                'role' => $m->role,
                'status' => $m->pivot->status,
                'is_co_teacher' => (bool) $m->pivot->is_co_teacher,
            ]);

        return response()->json([
            'classroom' => array_merge($this->serialize($classroom, $isOwner), [
                'teacher' => $classroom->teacher->name,
            ]),
            'members' => $members,
        ]);
    }

    public function update(Request $request, string $token): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $classroom = Classroom::where('invite_token', $token)->firstOrFail();

        if ($classroom->teacher_id !== $user->id) {
            return response()->json(['message' => 'Only the owner can edit this class.'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'course_type' => ['nullable', 'string', 'max:255'],
            'theme_color' => ['required', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'type' => ['required', Rule::in(['public', 'private'])],
            'join_approval' => ['sometimes', 'boolean'],
            'leave_approval' => ['sometimes', 'boolean'],
            'allow_posts' => ['sometimes', 'boolean'],
        ]);

        $classroom->update($validated);
        $classroom->loadCount('members');

        return response()->json(['classroom' => $this->serialize($classroom, true)]);
    }

    public function archive(string $token): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $classroom = Classroom::where('invite_token', $token)->firstOrFail();

        if ($classroom->teacher_id !== $user->id) {
            return response()->json(['message' => 'Only the owner can archive this class.'], 403);
        }

        $classroom->update(['archived_at' => now()]);

        return response()->json(['message' => 'Class archived.']);
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

        $status = $classroom->join_approval ? 'pending' : 'approved';
        $classroom->members()->attach($user->id, ['status' => $status]);

        return response()->json([
            'message' => $status === 'approved' ? 'Joined successfully.' : 'Join request sent. Awaiting approval.',
            'status' => $status,
        ]);
    }

    public function approveMember(string $token, int $userId): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $classroom = Classroom::where('invite_token', $token)->firstOrFail();

        if ($classroom->teacher_id !== $user->id) {
            return response()->json(['message' => 'Only the owner can approve members.'], 403);
        }

        $classroom->members()->updateExistingPivot($userId, ['status' => 'approved']);

        return response()->json(['message' => 'Member approved.']);
    }

    public function assignCoTeacher(string $token, int $userId): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $classroom = Classroom::where('invite_token', $token)->firstOrFail();

        if ($classroom->teacher_id !== $user->id) {
            return response()->json(['message' => 'Only the owner can assign co-teachers.'], 403);
        }

        $target = $classroom->members()->where('user_id', $userId)->first();
        if (! $target || $target->role !== 'teacher') {
            return response()->json(['message' => 'Co-teacher must be a teacher.'], 422);
        }

        $classroom->members()->updateExistingPivot($userId, ['is_co_teacher' => true]);

        return response()->json(['message' => 'Co-teacher assigned.']);
    }

    public function giveAward(Request $request, string $token, int $userId): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $classroom = Classroom::where('invite_token', $token)->firstOrFail();

        $isTeacher = $classroom->teacher_id === $user->id
            || $classroom->members()->where('user_id', $user->id)->wherePivot('is_co_teacher', true)->exists();

        if (! $isTeacher) {
            return response()->json(['message' => 'Only teachers can give awards.'], 403);
        }

        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        Award::create([
            'classroom_id' => $classroom->id,
            'user_id' => $userId,
            'given_by' => $user->id,
            'label' => $data['label'],
            'icon' => $data['icon'] ?? 'pi pi-star',
        ]);

        return response()->json(['message' => 'Award given.']);
    }

    public function removeMember(string $token, int $userId): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $classroom = Classroom::where('invite_token', $token)->firstOrFail();

        if ($classroom->teacher_id !== $user->id) {
            return response()->json(['message' => 'Only the owner can remove members.'], 403);
        }

        $classroom->members()->detach($userId);

        return response()->json(['message' => 'Member removed.']);
    }

    public function leave(string $token): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $classroom = Classroom::where('invite_token', $token)->firstOrFail();

        if ($classroom->teacher_id === $user->id) {
            $coTeacher = $classroom->members()->wherePivot('is_co_teacher', true)->first();
            if (! $coTeacher) {
                return response()->json(['message' => 'You must assign a Co-teacher before leaving the class!'], 422);
            }
            $classroom->update(['teacher_id' => $coTeacher->id]);
            $classroom->members()->updateExistingPivot($coTeacher->id, ['is_co_teacher' => false]);

            return response()->json(['message' => 'Ownership transferred. You left the class.']);
        }

        if ($classroom->leave_approval) {
            $classroom->members()->updateExistingPivot($user->id, ['status' => 'pending']);

            return response()->json(['message' => 'Leave request sent. Awaiting approval.']);
        }

        $classroom->members()->detach($user->id);

        return response()->json(['message' => 'You left the class.']);
    }

    public function userProfile(int $id): JsonResponse
    {
        $u = User::with('awards.classroom')->findOrFail($id);

        return response()->json([
            'user' => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'course' => $u->course,
            ],
            'awards' => $u->awards->map(fn ($a) => [
                'id' => $a->id,
                'label' => $a->label,
                'icon' => $a->icon,
                'class' => $a->classroom?->name,
                'date' => $a->created_at->format('M j, Y'),
            ]),
        ]);
    }

    public function notifications(): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $pending = $user->ownedClassrooms()
            ->whereNull('archived_at')
            ->withCount(['members as pending_count' => fn ($q) => $q->where('status', 'pending')])
            ->get()
            ->sum('pending_count');

        $awards = $user->awards()->count();

        return response()->json(['classes' => (int) $pending, 'awards' => (int) $awards]);
    }

    private function serialize(Classroom $c, bool $owner): array
    {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'description' => $c->description,
            'course_type' => $c->course_type,
            'theme_color' => $c->theme_color,
            'type' => $c->type,
            'invite_token' => $c->invite_token,
            'join_approval' => (bool) $c->join_approval,
            'leave_approval' => (bool) $c->leave_approval,
            'allow_posts' => (bool) $c->allow_posts,
            'members_count' => $c->members_count ?? 0,
            'is_owner' => $owner,
        ];
    }
}
