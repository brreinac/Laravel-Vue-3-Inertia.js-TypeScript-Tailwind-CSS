<?php

namespace App\Http\Controllers;

use App\Actions\ChangeTaskStatus;
use App\Enums\TaskStatus;
use App\Http\Requests\ChangeTaskStatusRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\TaskIndexRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Support\TaskFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskController extends Controller
{
    public function index(TaskIndexRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Task::class);
        $filters = $request->validated();

        return TaskResource::collection(
            TaskFilters::apply($this->visibleTasks($request->user()), $filters)
                ->with(['project', 'assignee'])
                ->latest()
                ->paginate($filters['per_page'] ?? 15)
                ->withQueryString(),
        );
    }

    public function store(StoreTaskRequest $request): TaskResource
    {
        $this->authorize('create', Task::class);
        $task = Task::create([
            ...$request->validated(),
            'status' => $request->validated('status') ?? TaskStatus::Pending,
        ]);

        return new TaskResource($task->load(['project', 'assignee']));
    }

    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);

        return new TaskResource($task->load([
            'project',
            'assignee',
            'comments.user',
            'activityLogs.user',
        ]));
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $this->authorize('update', $task);
        $data = $request->validated();

        if (! $request->user()->isAdmin()) {
            $data = array_intersect_key($data, array_flip(['title', 'description', 'priority', 'due_date']));
        }

        $task->update($data);

        return new TaskResource($task->fresh(['project', 'assignee']));
    }

    public function destroy(Task $task): \Illuminate\Http\Response
    {
        $this->authorize('delete', $task);
        $task->delete();

        return response()->noContent();
    }

    public function changeStatus(ChangeTaskStatusRequest $request, Task $task, ChangeTaskStatus $action): TaskResource
    {
        $this->authorize('changeStatus', $task);
        $updatedTask = $action->execute($task, $request->enum('status', TaskStatus::class), $request->user());

        return new TaskResource($updatedTask);
    }

    public function export(TaskIndexRequest $request): StreamedResponse
    {
        $this->authorize('viewAny', Task::class);
        $filters = $request->validated();
        $tasks = TaskFilters::apply($this->visibleTasks($request->user()), $filters)
            ->with(['project', 'assignee'])
            ->orderBy('id')
            ->cursor();

        return response()->streamDownload(function () use ($tasks): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Título', 'Proyecto', 'Asignado a', 'Estado', 'Prioridad', 'Fecha límite']);

            foreach ($tasks as $task) {
                fputcsv($handle, [
                    $task->id,
                    $task->title,
                    $task->project->name,
                    $task->assignee?->name,
                    $task->status->value,
                    $task->priority->value,
                    $task->due_date?->toDateString(),
                ]);
            }

            fclose($handle);
        }, 'tareas.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return Builder<Task> */
    private function visibleTasks(\App\Models\User $user): Builder
    {
        return Task::query()->when(! $user->isAdmin(), fn (Builder $query) => $query->where('assigned_to', $user->id));
    }
}
