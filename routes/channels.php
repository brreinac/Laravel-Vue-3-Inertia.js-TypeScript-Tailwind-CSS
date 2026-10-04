<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:api']]);

Broadcast::channel('projects.{projectId}', function (User $user, int $projectId): bool {
    return $user->isAdmin() || Task::query()
        ->where('project_id', $projectId)
        ->where('assigned_to', $user->id)
        ->exists();
});
