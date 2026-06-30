<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ClassroomModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClassroomModuleController extends Controller
{
    private function authorizeTeacher(string $token): array
    {
        $user = Auth::guard('api')->user();
        $classroom = Classroom::where('invite_token', $token)->firstOrFail();
        $isOwner = $classroom->teacher_id === $user->id;
        $isCoTeacher = $classroom->members()->where('user_id', $user->id)->wherePivot('is_co_teacher', true)->exists();

        return [$user, $classroom, $isOwner || $isCoTeacher];
    }

    public function index(string $token): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $classroom = Classroom::where('invite_token', $token)->firstOrFail();
        $isOwner = $classroom->teacher_id === $user->id;
        $isMember = $classroom->members()->where('user_id', $user->id)->wherePivot('status', 'approved')->exists();
        abort_unless($isOwner || $isMember, 403, 'No access to this class.');

        $isCoTeacher = $classroom->members()->where('user_id', $user->id)->wherePivot('is_co_teacher', true)->exists();
        $canManage = $isOwner || $isCoTeacher;

        $modules = $classroom->modules()
            ->with('author')
            ->when(! $canManage, fn ($q) => $q->whereNull('archived_at'))
            ->latest()
            ->get()
            ->map(fn ($m) => $this->serialize($m));

        return response()->json(['modules' => $modules, 'can_manage' => $canManage]);
    }

    public function store(Request $request, string $token): JsonResponse
    {
        [$user, $classroom, $canManage] = $this->authorizeTeacher($token);
        abort_unless($canManage, 403, 'Only teachers can add modules.');

        $data = $request->validate([
            'description' => ['required', 'string', 'max:5000'],
            'files' => ['nullable', 'array', 'max:20'],
            'files.*.url' => ['required_with:files', 'string', 'max:1000'],
            'files.*.name' => ['nullable', 'string', 'max:255'],
            'files.*.mime' => ['nullable', 'string', 'max:120'],
            'files.*.size' => ['nullable', 'integer'],
        ]);

        $files = collect($data['files'] ?? [])->map(fn ($f) => [
            'url' => $f['url'],
            'name' => $f['name'] ?? 'Attachment',
            'mime' => $f['mime'] ?? null,
            'size' => $f['size'] ?? null,
        ])->values()->all();

        $module = $classroom->modules()->create([
            'user_id' => $user->id,
            'description' => $data['description'],
            'files' => $files,
        ]);

        // Mirror to the class timeline.
        $fileLinks = collect($files)->map(fn ($f) => '<p><a href="'.e($f['url']).'" target="_blank" rel="noopener"><i class="pi pi-paperclip"></i> '.e($f['name']).'</a></p>')->implode('');
        $classroom->posts()->create([
            'user_id' => $user->id,
            'kind' => 'module',
            'body' => '<p><i class="pi pi-folder-open"></i> <strong>New module</strong></p>'.
                '<p>'.nl2br(e($module->description)).'</p>'.$fileLinks,
        ]);

        return response()->json(['module' => $this->serialize($module->load('author'))], 201);
    }

    public function archive(string $token, int $id): JsonResponse
    {
        [, $classroom, $canManage] = $this->authorizeTeacher($token);
        abort_unless($canManage, 403);

        $module = $classroom->modules()->findOrFail($id);
        $module->update(['archived_at' => $module->archived_at ? null : now()]);

        return response()->json(['archived' => (bool) $module->archived_at]);
    }

    public function destroy(string $token, int $id): JsonResponse
    {
        [, $classroom, $canManage] = $this->authorizeTeacher($token);
        abort_unless($canManage, 403);

        $module = $classroom->modules()->findOrFail($id);

        foreach (($module->files ?? []) as $f) {
            if (! empty($f['url']) && str_starts_with($f['url'], '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $f['url']));
            }
        }

        $module->delete();

        return response()->json(['message' => 'Module deleted.']);
    }

    private function serialize(ClassroomModule $m): array
    {
        return [
            'id' => $m->id,
            'description' => $m->description,
            'files' => $m->files ?? [],
            'author' => $m->author?->name,
            'avatar_url' => $m->author?->avatar(),
            'is_archived' => (bool) $m->archived_at,
            'date' => $m->created_at->diffForHumans(),
        ];
    }
}
