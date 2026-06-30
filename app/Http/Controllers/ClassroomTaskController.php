<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ClassroomTask;
use App\Models\ClassroomTaskSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassroomTaskController extends Controller
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

        $tasks = $classroom->tasks()
            ->with(['author', 'submissions' => fn ($q) => $q->where('user_id', $user->id)])
            ->withCount('submissions')
            ->when(! $canManage, fn ($q) => $q->whereNull('archived_at'))
            ->latest()
            ->get()
            ->filter(fn ($t) => $canManage || $this->visibleTo($t, $user->id))
            ->map(fn ($t) => $this->serialize($t, $user->id))
            ->values();

        return response()->json(['tasks' => $tasks, 'can_manage' => $canManage]);
    }

    public function store(Request $request, string $token): JsonResponse
    {
        [$user, $classroom, $canManage] = $this->authorizeTeacher($token);
        abort_unless($canManage, 403, 'Only teachers can add tasks.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'type' => ['required', 'in:quiz,activity,study,exam'],
            'description' => ['nullable', 'string', 'max:5000'],
            'deadline_type' => ['required', 'in:none,today,tomorrow,this_week,two_weeks,custom'],
            'deadline_at' => ['nullable', 'date'],
            'deadline_end' => ['nullable', 'date'],
            'duration' => ['nullable', 'string', 'max:20'],
            'visibility' => ['required', 'in:all,specific'],
            'visible_members' => ['nullable', 'array'],
            'visible_members.*' => ['integer'],
            'advanced' => ['nullable', 'array'],
            'questions' => ['nullable', 'array', 'max:200'],
            'questions.*.name' => ['required_with:questions', 'string', 'max:2000'],
            'questions.*.type' => ['required_with:questions', 'string', 'max:40'],
            'questions.*.options' => ['nullable', 'array'],
        ]);

        [$deadlineAt, $deadlineEnd] = $this->resolveDeadline($data);

        $task = $classroom->tasks()->create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'deadline_type' => $data['deadline_type'],
            'deadline_at' => $deadlineAt,
            'deadline_end' => $deadlineEnd,
            'duration' => $data['duration'] ?? null,
            'visibility' => $data['visibility'],
            'visible_members' => $data['visibility'] === 'specific' ? ($data['visible_members'] ?? []) : null,
            'advanced' => $data['advanced'] ?? null,
            'questions' => $data['questions'] ?? [],
        ]);

        // Mirror to the class timeline.
        $icons = ['quiz' => 'pi pi-question-circle', 'activity' => 'pi pi-pencil', 'study' => 'pi pi-book', 'exam' => 'pi pi-file-edit'];
        $icon = $icons[$task->type] ?? 'pi pi-check-square';
        $due = $task->deadline_at ? ' · due '.$task->deadline_at->format('M j, Y') : '';
        $classroom->posts()->create([
            'user_id' => $user->id,
            'kind' => 'task',
            'body' => '<p><i class="'.$icon.'"></i> <strong>New '.e(ucfirst($task->type)).': '.e($task->name).'</strong>'.e($due).'</p>'.
                ($task->description ? '<p>'.nl2br(e($task->description)).'</p>' : ''),
        ]);

        return response()->json(['task' => $this->serialize($task->load('author'))], 201);
    }

    public function archive(string $token, int $id): JsonResponse
    {
        [, $classroom, $canManage] = $this->authorizeTeacher($token);
        abort_unless($canManage, 403);

        $task = $classroom->tasks()->findOrFail($id);
        $task->update(['archived_at' => $task->archived_at ? null : now()]);

        return response()->json(['archived' => (bool) $task->archived_at]);
    }

    public function destroy(string $token, int $id): JsonResponse
    {
        [, $classroom, $canManage] = $this->authorizeTeacher($token);
        abort_unless($canManage, 403);

        $task = $classroom->tasks()->findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted.']);
    }

    private function visibleTo(ClassroomTask $t, int $userId): bool
    {
        if ($t->visibility !== 'specific') {
            return true;
        }

        return in_array($userId, $t->visible_members ?? [], true);
    }

    private function resolveDeadline(array $data): array
    {
        return match ($data['deadline_type']) {
            'today' => [now()->endOfDay(), null],
            'tomorrow' => [now()->addDay()->endOfDay(), null],
            'this_week' => [now()->endOfWeek(), null],
            'two_weeks' => [now()->addWeeks(2)->endOfDay(), null],
            'custom' => [
                isset($data['deadline_at']) ? \Carbon\Carbon::parse($data['deadline_at']) : null,
                isset($data['deadline_end']) ? \Carbon\Carbon::parse($data['deadline_end']) : null,
            ],
            default => [null, null],
        };
    }

    private function serialize(ClassroomTask $t, ?int $userId = null, bool $canManage = true): array
    {
        $questions = $t->questions ?? [];
        if (! $canManage) {
            // Hide the correct-answer flags from students.
            $questions = array_map(function ($q) {
                if (! empty($q['options']) && is_array($q['options'])) {
                    $q['options'] = array_map(fn ($o) => ['text' => $o['text'] ?? ''], $q['options']);
                }

                return $q;
            }, $questions);
        }

        $mySub = $t->relationLoaded('submissions') ? $t->submissions->firstWhere('user_id', $userId) : null;

        return [
            'id' => $t->id,
            'name' => $t->name,
            'type' => $t->type,
            'description' => $t->description,
            'deadline_type' => $t->deadline_type,
            'deadline_at' => $t->deadline_at?->toIso8601String(),
            'deadline_end' => $t->deadline_end?->toIso8601String(),
            'deadline_label' => $this->deadlineLabel($t),
            'duration' => $t->duration,
            'visibility' => $t->visibility,
            'visible_members' => $t->visible_members ?? [],
            'advanced' => $t->advanced ?? [],
            'questions' => $questions,
            'questions_count' => count($t->questions ?? []),
            'objective_total' => $this->objectiveTotal($t),
            'submissions_count' => $t->submissions_count ?? null,
            'my_status' => $mySub?->status,
            'my_score' => $mySub?->score,
            'author' => $t->author?->name,
            'avatar_url' => $t->author?->avatar(),
            'is_archived' => (bool) $t->archived_at,
            'date' => $t->created_at->diffForHumans(),
        ];
    }

    private function objectiveTotal(ClassroomTask $t): int
    {
        return collect($t->questions ?? [])->filter(fn ($q) => in_array($q['type'] ?? '', ['radio', 'checkbox'], true))->count();
    }

    private function deadlineLabel(ClassroomTask $t): string
    {
        if ($t->deadline_type === 'none') {
            return 'No deadline';
        }
        if ($t->deadline_type === 'custom' && $t->deadline_at && $t->deadline_end) {
            return $t->deadline_at->format('M j').' – '.$t->deadline_end->format('M j, Y');
        }

        return $t->deadline_at ? 'Due '.$t->deadline_at->format('M j, Y g:i A') : 'No deadline';
    }

    // ---- Submissions / exam taking ----

    private function authorizeStudent(string $token, int $taskId): array
    {
        $user = Auth::guard('api')->user();
        $classroom = Classroom::where('invite_token', $token)->firstOrFail();
        $isOwner = $classroom->teacher_id === $user->id;
        $isMember = $classroom->members()->where('user_id', $user->id)->wherePivot('status', 'approved')->exists();
        abort_unless($isOwner || $isMember, 403, 'No access to this class.');

        $task = $classroom->tasks()->findOrFail($taskId);
        abort_unless($this->visibleTo($task, $user->id), 403, 'This task is not available to you.');

        return [$user, $classroom, $task];
    }

    public function mySubmission(string $token, int $id): JsonResponse
    {
        [$user, , $task] = $this->authorizeStudent($token, $id);

        $sub = ClassroomTaskSubmission::where('task_id', $task->id)->where('user_id', $user->id)->first();

        return response()->json(['submission' => $sub ? $this->serializeSubmission($sub, $task) : null]);
    }

    public function saveSubmission(Request $request, string $token, int $id): JsonResponse
    {
        [$user, , $task] = $this->authorizeStudent($token, $id);

        $data = $request->validate([
            'answers' => ['nullable', 'array'],
            'logs' => ['nullable', 'array'],
        ]);

        $sub = ClassroomTaskSubmission::firstOrNew([
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);

        if ($sub->status === 'submitted') {
            return response()->json(['message' => 'Already submitted.'], 422);
        }

        $sub->answers = $data['answers'] ?? $sub->answers ?? [];
        $sub->logs = $data['logs'] ?? $sub->logs ?? [];
        $sub->status = 'in_progress';
        $sub->started_at = $sub->started_at ?? now();
        $sub->save();

        return response()->json(['saved' => true]);
    }

    public function submitSubmission(Request $request, string $token, int $id): JsonResponse
    {
        [$user, , $task] = $this->authorizeStudent($token, $id);

        $data = $request->validate([
            'answers' => ['nullable', 'array'],
            'logs' => ['nullable', 'array'],
        ]);

        $sub = ClassroomTaskSubmission::firstOrNew([
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);

        if ($sub->status === 'submitted') {
            return response()->json(['message' => 'Already submitted.'], 422);
        }

        $answers = $data['answers'] ?? $sub->answers ?? [];
        [$correct] = $this->grade($task, $answers);

        $sub->answers = $answers;
        $sub->logs = $data['logs'] ?? $sub->logs ?? [];
        $sub->status = 'submitted';
        $sub->score = $correct;
        $sub->started_at = $sub->started_at ?? now();
        $sub->submitted_at = now();
        $sub->save();

        return response()->json(['submission' => $this->serializeSubmission($sub, $task)]);
    }

    public function submissions(string $token, int $id): JsonResponse
    {
        [, $classroom, $canManage] = $this->authorizeTeacher($token);
        abort_unless($canManage, 403);

        $task = $classroom->tasks()->findOrFail($id);
        $subs = ClassroomTaskSubmission::with('student')
            ->where('task_id', $task->id)
            ->latest('updated_at')
            ->get()
            ->map(fn ($s) => $this->serializeSubmission($s, $task, true));

        return response()->json([
            'task' => $this->serialize($task, null, true),
            'submissions' => $subs,
        ]);
    }

    private function grade(ClassroomTask $task, array $answers): array
    {
        $correct = 0;
        $total = 0;
        foreach (($task->questions ?? []) as $i => $q) {
            $type = $q['type'] ?? '';
            if (! in_array($type, ['radio', 'checkbox'], true)) {
                continue;
            }
            $total++;
            $correctSet = collect($q['options'] ?? [])->filter(fn ($o) => ! empty($o['correct']))->map(fn ($o) => $o['text'] ?? '')->values()->all();
            $ans = $answers[$i] ?? ($answers[(string) $i] ?? null);

            if ($type === 'radio') {
                if (is_string($ans) && count($correctSet) === 1 && $ans === $correctSet[0]) {
                    $correct++;
                }
            } else {
                $given = is_array($ans) ? $ans : [];
                sort($given);
                $want = $correctSet;
                sort($want);
                if (! empty($want) && $given === $want) {
                    $correct++;
                }
            }
        }

        return [$correct, $total];
    }

    private function serializeSubmission(ClassroomTaskSubmission $s, ClassroomTask $task, bool $withStudent = false): array
    {
        $out = [
            'id' => $s->id,
            'answers' => $s->answers ?? [],
            'logs' => $s->logs ?? [],
            'status' => $s->status,
            'score' => $s->score,
            'objective_total' => $this->objectiveTotal($task),
            'started_at' => $s->started_at?->toIso8601String(),
            'submitted_at' => $s->submitted_at?->diffForHumans(),
        ];

        if ($withStudent) {
            $out['student'] = $s->student?->name;
            $out['avatar_url'] = $s->student?->avatar();
        }

        return $out;
    }
}
