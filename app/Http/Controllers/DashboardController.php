<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = $this->visibleTasks($request->user());
        $byStatus = (clone $query)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statistics = collect(TaskStatus::cases())->mapWithKeys(
            fn (TaskStatus $status): array => [$status->value => (int) ($byStatus[$status->value] ?? 0)],
        );

        $upcoming = (clone $query)
            ->with(['project', 'assignee'])
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [today(), today()->addDays(7)])
            ->where('status', '!=', TaskStatus::Completed->value)
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return response()->json([
            'statistics' => $statistics,
            'upcoming_tasks' => TaskResource::collection($upcoming),
        ]);
    }

    /** @return Builder<Task> */
    private function visibleTasks(\App\Models\User $user): Builder
    {
        return Task::query()->when(! $user->isAdmin(), fn (Builder $query) => $query->where('assigned_to', $user->id));
    }
}
