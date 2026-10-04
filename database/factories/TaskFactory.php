<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Task> */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => ucfirst(fake()->words(4, true)),
            'description' => fake()->paragraph(),
            'status' => TaskStatus::Pending,
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'project_id' => Project::factory(),
            'assigned_to' => User::factory(),
            'due_date' => fake()->dateTimeBetween('tomorrow', '+30 days'),
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => TaskStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}
