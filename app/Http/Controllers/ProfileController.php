<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(string $id): JsonResponse
    {
        $u = ctype_digit($id)
            ? User::with('awards.classroom')->findOrFail((int) $id)
            : User::with('awards.classroom')->where('profile_token', $id)->firstOrFail();
        $auth = Auth::guard('api')->user();
        $isSelf = $auth && $auth->id === $u->id;

        if ($isSelf) {
            $u->awards()->whereNull('seen_at')->update(['seen_at' => now()]);
        }

        $isFriend = $auth && Friendship::where('user_id', $auth->id)->where('friend_id', $u->id)->where('status', 'accepted')->exists();

        $teaching = collect();
        if ($u->role === 'teacher') {
            // Classes the user owns (position: Teacher)
            $teaching = $u->ownedClassrooms()->whereNull('archived_at')->get()->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'course_type' => $c->course_type,
                'position' => 'Teacher',
                'is_public' => (bool) $c->show_on_profile,
            ]);

            // Classes the user co-teaches (position: Co-teacher)
            $coTaught = $u->classrooms()
                ->wherePivot('status', 'approved')
                ->wherePivot('is_co_teacher', true)
                ->whereNull('archived_at')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'course_type' => $c->course_type,
                    'position' => 'Co-teacher',
                    'is_public' => (bool) $c->pivot->show_on_profile,
                ]);

            $teaching = $teaching->concat($coTaught);

            if (! $isSelf) {
                $teaching = $teaching->filter(fn ($t) => $t['is_public']);
            }
            $teaching = $teaching->values();
        }

        return response()->json([
            'user' => [
                'id' => $u->id,
                'profile_token' => $u->profile_token,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'course' => $u->course,
                'subject' => $u->subject,
                'city_municipality' => $u->city_municipality,
                'province' => $u->province,
                'country' => $u->country,
                'avatar_url' => $u->avatar(),
                'cover_url' => $u->cover_url,
                'friends_count' => $u->friends()->wherePivot('status', 'accepted')->count(),
                'is_self' => $isSelf,
                'is_friend' => (bool) $isFriend,
                'friend_status' => $auth ? self::status($auth->id, $u->id) : 'none',
            ],
            'teaching' => $teaching,
            'awards' => $u->role === 'teacher' ? [] : $u->awards->map(fn ($a) => [
                'id' => $a->id,
                'label' => $a->label,
                'icon' => $a->icon,
                'class' => $a->classroom?->name,
                'date' => $a->created_at->format('M j, Y'),
            ]),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $data = $request->validate([
            'avatar_url' => ['nullable', 'string', 'max:30000000'],
            'cover_url' => ['nullable', 'string', 'max:30000000'],
            'name' => ['nullable', 'string', 'max:100'],
            'subject' => ['nullable', 'string', 'max:100'],
            'city_municipality' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (! empty($data['password'])) {
            if (empty($data['current_password']) || ! Hash::check($data['current_password'], $user->password)) {
                return response()->json(['message' => 'Current password is incorrect.'], 422);
            }
            $user->password = $data['password'];
        }

        unset($data['password'], $data['current_password']);
        $user->fill(array_filter($data, fn ($v) => $v !== null))->save();

        return response()->json([
            'message' => 'Profile updated.',
            'user' => [
                'name' => $user->name,
                'subject' => $user->subject,
                'city_municipality' => $user->city_municipality,
                'province' => $user->province,
                'country' => $user->country,
            ],
        ]);
    }

    public function classVisibility(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $data = $request->validate([
            'class_id' => ['required', 'integer'],
            'public' => ['required', 'boolean'],
        ]);

        $classroom = Classroom::findOrFail($data['class_id']);

        if ($classroom->teacher_id === $user->id) {
            $classroom->update(['show_on_profile' => $data['public']]);

            return response()->json(['message' => 'Visibility updated.', 'is_public' => $data['public']]);
        }

        $isCoTeacher = $classroom->members()
            ->where('user_id', $user->id)
            ->wherePivot('is_co_teacher', true)
            ->exists();

        abort_unless($isCoTeacher, 403);

        $classroom->members()->updateExistingPivot($user->id, ['show_on_profile' => $data['public']]);

        return response()->json(['message' => 'Visibility updated.', 'is_public' => $data['public']]);
    }

    public function addFriend(int $id): JsonResponse
    {
        $user = Auth::guard('api')->user();
        if ($user->id === $id) {
            return response()->json(['message' => 'You cannot add yourself.'], 422);
        }

        $incoming = Friendship::where('user_id', $id)->where('friend_id', $user->id)->first();
        if ($incoming) {
            return $this->accept($id);
        }

        Friendship::firstOrCreate(['user_id' => $user->id, 'friend_id' => $id], ['status' => 'pending']);

        return response()->json(['status' => 'requested', 'message' => 'Request sent.']);
    }

    public function accept(int $id): JsonResponse
    {
        $user = Auth::guard('api')->user();
        Friendship::updateOrCreate(['user_id' => $id, 'friend_id' => $user->id], ['status' => 'accepted']);
        Friendship::updateOrCreate(['user_id' => $user->id, 'friend_id' => $id], ['status' => 'accepted']);

        return response()->json(['status' => 'friends', 'message' => 'Friend accepted.']);
    }

    public function decline(int $id): JsonResponse
    {
        $user = Auth::guard('api')->user();
        Friendship::where('user_id', $id)->where('friend_id', $user->id)->delete();

        return response()->json(['status' => 'none', 'message' => 'Request declined.']);
    }

    public function cancel(int $id): JsonResponse
    {
        $user = Auth::guard('api')->user();
        Friendship::where('user_id', $user->id)->where('friend_id', $id)->delete();
        Friendship::where('user_id', $id)->where('friend_id', $user->id)->where('status', 'accepted')->delete();

        return response()->json(['status' => 'none', 'message' => 'Removed.']);
    }

    public function unfriend(int $id): JsonResponse
    {
        $user = Auth::guard('api')->user();
        Friendship::where('user_id', $user->id)->where('friend_id', $id)->delete();
        Friendship::where('user_id', $id)->where('friend_id', $user->id)->delete();

        return response()->json(['status' => 'none', 'message' => 'Unfriended.']);
    }

    public static function status(int $authId, int $otherId): string
    {
        $out = Friendship::where('user_id', $authId)->where('friend_id', $otherId)->first();
        $in = Friendship::where('user_id', $otherId)->where('friend_id', $authId)->first();

        if (($out && $out->status === 'accepted') || ($in && $in->status === 'accepted')) {
            return 'friends';
        }
        if ($out) {
            return 'requested';
        }
        if ($in) {
            return 'incoming';
        }

        return 'none';
    }

    public function search(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json(['users' => []]);
        }

        $users = User::where('id', '!=', $user->id)
            ->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
            ->limit(20)->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'profile_token' => $u->profile_token,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'avatar_url' => $u->avatar(),
                'friend_status' => self::status($user->id, $u->id),
            ]);

        return response()->json(['users' => $users]);
    }
}
