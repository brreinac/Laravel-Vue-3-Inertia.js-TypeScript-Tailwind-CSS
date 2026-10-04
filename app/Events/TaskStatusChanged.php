<?php

namespace App\Events;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskStatusChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Task $task,
        public readonly TaskStatus $previousStatus,
        public readonly TaskStatus $newStatus,
        public readonly User $actor,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("projects.{$this->task->project_id}")];
    }

    public function broadcastAs(): string
    {
        return 'task.status.changed';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'task' => [
                'id' => $this->task->id,
                'project_id' => $this->task->project_id,
                'status' => $this->newStatus->value,
                'completed_at' => $this->task->completed_at?->toISOString(),
                'updated_at' => $this->task->updated_at?->toISOString(),
            ],
            'previous_status' => $this->previousStatus->value,
            'new_status' => $this->newStatus->value,
            'actor_id' => $this->actor->id,
        ];
    }
}
