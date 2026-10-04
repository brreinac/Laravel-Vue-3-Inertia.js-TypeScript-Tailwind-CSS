<?php

namespace App\Http\Controllers;

use App\Http\Requests\GlobalSearchRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class GlobalSearchController extends Controller
{
    public function __invoke(GlobalSearchRequest $request): JsonResponse
    {
        $term = $request->validated('q');
        $user = $request->user();

        $projects = Project::query()
            ->when(! $user->isAdmin(), fn ($query) => $query->whereHas('tasks', fn ($tasks) => $tasks->where('assigned_to', $user->id)))
            ->where('name', 'like', "%{$term}%")
            ->with('owner')
            ->limit(5)
            ->get();

        $tasks = Task::query()
            ->when(! $user->isAdmin(), fn ($query) => $query->where('assigned_to', $user->id))
            ->where(fn ($query) => $query->where('title', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"))
            ->with(['project', 'assignee'])
            ->limit(5)
            ->get();

        $users = User::query()
            ->where(fn ($query) => $query->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
            ->limit(5)
            ->get();

        return response()->json([
            'projects' => ProjectResource::collection($projects),
            'tasks' => TaskResource::collection($tasks),
            'users' => UserResource::collection($users),
        ]);
    }
}
