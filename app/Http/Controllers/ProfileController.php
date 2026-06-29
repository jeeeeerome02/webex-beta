<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $u = User::with('awards.classroom')->findOrFail($id);
        $auth = Auth::guard('api')->user();

        if ($auth && $auth->id === $u->id) {
            $u->awards()->whereNull('seen_at')->update(['seen_at' => now()]);
        }

        $isFriend = $auth && Friendship::where('user_id', $auth->id)->where('friend_id', $u->id)->where('status', 'accepted')->exists();

        return response()->json([
            'user' => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'course' => $u->course,
                'avatar_url' => $u->avatar(),
                'cover_url' => $u->cover_url,
                'friends_count' => $u->friends()->wherePivot('status', 'accepted')->count(),
                'is_self' => $auth && $auth->id === $u->id,
                'is_friend' => (bool) $isFriend,
                'friend_status' => $auth ? self::status($auth->id, $u->id) : 'none',
            ],
            'teaching' => $u->role === 'teacher'
                ? $u->ownedClassrooms()->whereNull('archived_at')->get()->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'course_type' => $c->course_type,
                ])
                : [],
            'awards' => $u->awards->map(fn ($a) => [
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
            'course' => ['nullable', 'string', 'max:100'],
        ]);

        $user->fill(array_filter($data, fn ($v) => $v !== null))->save();

        return response()->json(['message' => 'Profile updated.']);
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
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'avatar_url' => $u->avatar(),
                'friend_status' => self::status($user->id, $u->id),
            ]);

        return response()->json(['users' => $users]);
    }
}
