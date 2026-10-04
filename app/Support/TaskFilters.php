<?php

namespace App\Support;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;

class TaskFilters
{
    /** @param Builder<Task> $query @param array<string, mixed> $filters @return Builder<Task> */
    public static function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn (Builder $builder, string $term) => $builder->where(function (Builder $nested) use ($term): void {
                $nested->where('title', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%");
            }))
            ->when($filters['status'] ?? null, fn (Builder $builder, string $status) => $builder->where('status', $status))
            ->when($filters['priority'] ?? null, fn (Builder $builder, string $priority) => $builder->where('priority', $priority))
            ->when($filters['project_id'] ?? null, fn (Builder $builder, int $projectId) => $builder->where('project_id', $projectId))
            ->when($filters['assigned_to'] ?? null, fn (Builder $builder, int $assigneeId) => $builder->where('assigned_to', $assigneeId));
    }
}
