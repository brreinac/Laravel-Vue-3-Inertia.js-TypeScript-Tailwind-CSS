<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Ana Administradora',
            'email' => 'admin@qvox.local',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        $members = collect([
            ['name' => 'Mateo Miembro', 'email' => 'mateo@qvox.local'],
            ['name' => 'Sofía Miembro', 'email' => 'sofia@qvox.local'],
        ])->map(fn (array $user): User => User::factory()->create([
            ...$user,
            'role' => 'member',
            'password' => Hash::make('password'),
        ]));

        $projects = collect([
            ['name' => 'Portal de clientes', 'description' => 'Rediseño del autoservicio de clientes.', 'status' => ProjectStatus::Active],
            ['name' => 'Automatización operativa', 'description' => 'Flujos internos para reducir trabajo manual.', 'status' => ProjectStatus::Active],
        ])->map(fn (array $project): Project => Project::factory()->create([
            ...$project,
            'owner_id' => $admin->id,
        ]));

        $statuses = [
            TaskStatus::Pending,
            TaskStatus::InProgress,
            TaskStatus::InReview,
            TaskStatus::Completed,
        ];

        collect(range(1, 10))->each(function (int $position) use ($projects, $members, $statuses): void {
            $status = $statuses[$position % count($statuses)];
            $task = Task::factory()->create([
                'title' => "Tarea de prueba {$position}",
                'project_id' => $projects[$position % $projects->count()]->id,
                'assigned_to' => $members[$position % $members->count()]->id,
                'status' => $status,
                'priority' => TaskPriority::cases()[$position % count(TaskPriority::cases())],
                'due_date' => now()->addDays($position),
                'completed_at' => $status === TaskStatus::Completed ? now()->subDay() : null,
            ]);

            Comment::factory()->create([
                'task_id' => $task->id,
                'user_id' => $task->assigned_to,
                'body' => 'Avance inicial registrado para la tarea.',
            ]);

            ActivityLog::create([
                'task_id' => $task->id,
                'user_id' => $task->assigned_to,
                'old_status' => null,
                'new_status' => $status,
                'description' => 'Estado inicial de la tarea.',
            ]);
        });
    }
}
