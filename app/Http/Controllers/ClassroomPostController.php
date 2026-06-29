<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ClassroomPost;
use App\Models\ClassroomPostComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassroomPostController extends Controller
{
    private function authorize(string $token): array
    {
        $user = Auth::guard('api')->user();
        $classroom = Classroom::where('invite_token', $token)->firstOrFail();
        $isOwner = $classroom->teacher_id === $user->id;
        $isMember = $classroom->members()->where('user_id', $user->id)->wherePivot('status', 'approved')->exists();

        abort_unless($isOwner || $isMember, 403, 'No access to this class.');

        return [$user, $classroom, $isOwner];
    }

    public function index(string $token): JsonResponse
    {
        [$user, $classroom, $isOwner] = $this->authorize($token);

        $posts = $classroom->posts()
            ->with(['author', 'comments.author', 'comments.likes', 'comments.replies.author', 'comments.replies.likes', 'likes'])
            ->withCount('likes')
            ->orderByDesc('is_pinned')
            ->latest()
            ->get()
            ->filter(fn ($p) => ! $p->is_hidden || $isOwner || $p->user_id === $user->id)
            ->map(fn ($p) => $this->serialize($p, $user->id, $isOwner))
            ->values();

        return response()->json(['posts' => $posts]);
    }

    public function store(Request $request, string $token): JsonResponse
    {
        [$user, $classroom, $isOwner] = $this->authorize($token);

        if (! $isOwner && ! $classroom->allow_posts) {
            return response()->json(['message' => 'Posting is disabled for members.'], 403);
        }

        $data = $request->validate(['body' => ['required', 'string', 'max:35000000']]);

        $clean = strip_tags($data['body'], '<p><br><b><strong><i><em><u><s><ul><ol><li><a><h1><h2><h3><blockquote><pre><span><img>');
        $post = $classroom->posts()->create(['user_id' => $user->id, 'body' => $clean]);
        $post->loadCount('likes')->load(['author', 'comments.author', 'likes']);

        return response()->json(['post' => $this->serialize($post, $user->id, $isOwner)], 201);
    }

    public function like(string $token, int $postId): JsonResponse
    {
        [$user] = $this->authorize($token);
        $post = ClassroomPost::findOrFail($postId);

        $existing = $post->likes()->where('user_id', $user->id)->first();
        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            $post->likes()->create(['user_id' => $user->id]);
            $liked = true;
        }

        return response()->json(['liked' => $liked, 'count' => $post->likes()->count()]);
    }

    public function comment(Request $request, string $token, int $postId): JsonResponse
    {
        [$user] = $this->authorize($token);
        $post = ClassroomPost::findOrFail($postId);

        if (! $post->comments_enabled) {
            return response()->json(['message' => 'Comments are turned off.'], 403);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer'],
        ]);
        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'body' => $data['body'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);
        $comment->load(['author', 'likes', 'replies']);

        return response()->json(['comment' => $this->serializeComment($comment, $user->id)]);
    }

    public function likeComment(string $token, int $postId, int $commentId): JsonResponse
    {
        [$user] = $this->authorize($token);
        $comment = ClassroomPostComment::findOrFail($commentId);

        $existing = $comment->likes()->where('user_id', $user->id)->first();
        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            $comment->likes()->create(['user_id' => $user->id]);
            $liked = true;
        }

        return response()->json(['liked' => $liked, 'count' => $comment->likes()->count()]);
    }

    public function toggleComments(string $token, int $postId): JsonResponse
    {
        [$user, , $isOwner] = $this->authorize($token);
        $post = ClassroomPost::findOrFail($postId);
        abort_unless($isOwner || $post->user_id === $user->id, 403);

        $post->update(['comments_enabled' => ! $post->comments_enabled]);

        return response()->json(['comments_enabled' => $post->comments_enabled]);
    }

    public function hide(string $token, int $postId): JsonResponse
    {
        [$user, , $isOwner] = $this->authorize($token);
        $post = ClassroomPost::findOrFail($postId);
        abort_unless($isOwner || $post->user_id === $user->id, 403);

        $post->update(['is_hidden' => ! $post->is_hidden]);

        return response()->json(['is_hidden' => $post->is_hidden]);
    }

    public function pin(string $token, int $postId): JsonResponse
    {
        [$user, $classroom, $isOwner] = $this->authorize($token);
        $post = ClassroomPost::findOrFail($postId);

        $isCoTeacher = $classroom->members()->where('user_id', $user->id)->wherePivot('is_co_teacher', true)->exists();
        abort_unless($isOwner || $isCoTeacher, 403, 'Only teachers can pin posts.');

        $post->update(['is_pinned' => ! $post->is_pinned]);

        return response()->json(['is_pinned' => $post->is_pinned]);
    }

    private function serialize(ClassroomPost $p, int $uid, bool $isOwner): array
    {
        return [
            'id' => $p->id,
            'body' => $p->body,
            'author' => $p->author->name,
            'author_id' => $p->user_id,
            'avatar_url' => $p->author->avatar(),
            'is_mine' => $p->user_id === $uid,
            'is_hidden' => $p->is_hidden,
            'is_pinned' => $p->is_pinned,
            'comments_enabled' => $p->comments_enabled,
            'likes_count' => $p->likes_count ?? $p->likes()->count(),
            'liked' => $p->likes->contains('user_id', $uid),
            'created_at' => $p->created_at->diffForHumans(),
            'comments' => $p->comments->where('parent_id', null)->map(fn ($c) => $this->serializeComment($c, $uid))->values(),
        ];
    }

    private function serializeComment(ClassroomPostComment $c, int $uid): array
    {
        return [
            'id' => $c->id,
            'body' => $c->body,
            'author' => $c->author->name,
            'author_id' => $c->user_id,
            'avatar_url' => $c->author->avatar(),
            'likes_count' => $c->likes->count(),
            'liked' => $c->likes->contains('user_id', $uid),
            'replies' => $c->replies->map(fn ($r) => [
                'id' => $r->id, 'body' => $r->body, 'author' => $r->author->name,
                'author_id' => $r->user_id, 'avatar_url' => $r->author->avatar(),
                'likes_count' => $r->likes->count(), 'liked' => $r->likes->contains('user_id', $uid),
            ])->values(),
        ];
    }
}
