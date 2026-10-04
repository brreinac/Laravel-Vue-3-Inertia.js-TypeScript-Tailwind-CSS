<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Http\Requests\ProjectIndexRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\TaskIndexRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Support\TaskFilters;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    public function index(ProjectIndexRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Project::class);

        $query = Project::query()->with('owner')->latest();

        if (! $request->user()->isAdmin()) {
            $query->whereHas('tasks', fn ($tasks) => $tasks->where('assigned_to', $request->user()->id));
        }

        $filters = $request->validated();
        $query->filter($filters['search'] ?? null, $filters['status'] ?? null);

        return ProjectResource::collection($query->paginate($filters['per_page'] ?? 15)->withQueryString());
    }

    public function store(StoreProjectRequest $request): ProjectResource
    {
        $this->authorize('create', Project::class);

        $project = Project::create([
            ...$request->validated(),
            'status' => $request->validated('status') ?? ProjectStatus::Active,
            'owner_id' => $request->validated('owner_id') ?? $request->user()->id,
        ]);

        return new ProjectResource($project->load('owner'));
    }

    public function show(Project $project): ProjectResource
    {
        $this->authorize('view', $project);

        return new ProjectResource($project->load('owner'));
    }

    public function update(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        $this->authorize('update', $project);
        $project->update($request->validated());

        return new ProjectResource($project->fresh('owner'));
    }

    public function destroy(Project $project): ProjectResource
    {
        $this->authorize('delete', $project);
        $project->update(['status' => ProjectStatus::Archived]);

        return new ProjectResource($project->fresh('owner'));
    }

    public function tasks(TaskIndexRequest $request, Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);
        $filters = $request->validated();

        return TaskResource::collection(
            TaskFilters::apply(Task::query()->where('project_id', $project->id), $filters)
                ->with('assignee')
                ->latest()
                ->paginate($filters['per_page'] ?? 15)
                ->withQueryString(),
        );
    }
}
