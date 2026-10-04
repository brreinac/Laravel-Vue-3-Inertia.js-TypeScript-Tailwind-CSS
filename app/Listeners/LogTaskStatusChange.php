<?php

namespace App\Listeners;

use App\Events\TaskStatusChanged;
use App\Models\ActivityLog;
use App\Notifications\TaskStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogTaskStatusChange implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(TaskStatusChanged $event): void
    {
        ActivityLog::create([
            'task_id' => $event->task->id,
            'user_id' => $event->actor->id,
            'old_status' => $event->previousStatus,
            'new_status' => $event->newStatus,
            'description' => "Estado cambiado de {$event->previousStatus->value} a {$event->newStatus->value}.",
        ]);

        if ($event->task->assigned_to !== null) {
            $event->task->assignee?->notify(new TaskStatusChangedNotification(
                $event->task,
                $event->previousStatus,
                $event->newStatus,
            ));
        }
    }
}
