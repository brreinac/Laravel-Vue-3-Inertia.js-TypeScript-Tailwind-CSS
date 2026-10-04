<?php

namespace App\Actions;

use App\Enums\TaskStatus;
use App\Events\TaskStatusChanged;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeTaskStatus
{
    /** @var array<string, list<TaskStatus>> */
    private const TRANSITIONS = [
        'pendiente' => [TaskStatus::InProgress],
        'en_progreso' => [TaskStatus::Pending, TaskStatus::InReview],
        'en_revision' => [TaskStatus::InProgress, TaskStatus::Completed],
        'completada' => [TaskStatus::InReview],
    ];

    public function execute(Task $task, TaskStatus $nextStatus, User $actor): Task
    {
        return DB::transaction(function () use ($task, $nextStatus, $actor): Task {
            $lockedTask = Task::query()->lockForUpdate()->findOrFail($task->id);
            $previousStatus = $lockedTask->status;

            if ($previousStatus === $nextStatus) {
                return $lockedTask;
            }

            if (! in_array($nextStatus, self::TRANSITIONS[$previousStatus->value], true)) {
                throw ValidationException::withMessages([
                    'status' => "La transición de {$previousStatus->value} a {$nextStatus->value} no está permitida.",
                ]);
            }

            $lockedTask->update([
                'status' => $nextStatus,
                'completed_at' => $nextStatus === TaskStatus::Completed ? now() : null,
            ]);

            TaskStatusChanged::dispatch($lockedTask, $previousStatus, $nextStatus, $actor);

            return $lockedTask->fresh(['project', 'assignee']);
        });
    }
}
