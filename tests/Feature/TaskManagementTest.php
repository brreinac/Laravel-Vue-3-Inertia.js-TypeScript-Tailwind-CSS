<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Events\TaskStatusChanged;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_task(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create(['owner_id' => $admin->id]);
        $member = User::factory()->create();

        $response = $this->actingAs($admin, 'api')->postJson('/api/tasks', [
            'title' => 'Preparar demostración',
            'description' => 'Flujo principal de la aplicación.',
            'priority' => 'alta',
            'project_id' => $project->id,
            'assigned_to' => $member->id,
            'due_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertOk()->assertJsonPath('data.title', 'Preparar demostración');
        $this->assertDatabaseHas('tasks', ['title' => 'Preparar demostración', 'project_id' => $project->id]);
    }

    public function test_invalid_task_data_cannot_create_a_task(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'api')->postJson('/api/tasks', [
            'title' => '',
            'priority' => 'urgente',
            'project_id' => 999999,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['title', 'priority', 'project_id']);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_valid_status_transition_updates_task_and_dispatches_event(): void
    {
        Event::fake();
        $admin = User::factory()->admin()->create();
        $task = Task::factory()->create(['status' => TaskStatus::Pending]);

        $response = $this->actingAs($admin, 'api')->patchJson("/api/tasks/{$task->id}/status", ['status' => 'en_progreso']);

        $response->assertOk()->assertJsonPath('data.status', 'en_progreso');
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'en_progreso', 'completed_at' => null]);
        Event::assertDispatched(TaskStatusChanged::class);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $task = Task::factory()->create(['status' => TaskStatus::Pending]);

        $response = $this->actingAs($admin, 'api')->patchJson("/api/tasks/{$task->id}/status", ['status' => 'completada']);

        $response->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'pendiente']);
    }

    public function test_member_cannot_manage_projects(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member, 'api')->postJson('/api/projects', [
            'name' => 'Proyecto no autorizado',
            'status' => 'activo',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('projects', 0);
    }

    public function test_member_can_comment_only_on_an_assigned_task(): void
    {
        $member = User::factory()->create();
        $assigned = Task::factory()->create(['assigned_to' => $member->id]);
        $otherTask = Task::factory()->create();

        $this->actingAs($member, 'api')->postJson("/api/tasks/{$assigned->id}/comments", ['body' => 'Avancé con este punto.'])
            ->assertOk()
            ->assertJsonPath('data.body', 'Avancé con este punto.');

        $this->actingAs($member, 'api')->postJson("/api/tasks/{$otherTask->id}/comments", ['body' => 'No debería guardarse.'])
            ->assertForbidden();
    }
}
