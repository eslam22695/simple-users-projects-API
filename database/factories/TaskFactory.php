<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'status' => fake()->randomElement(TaskStatus::cases()),
            'due_date' => fake()->optional()->dateTimeBetween('-1 week', '+1 month'),
        ];
    }

    public function overdue(): static
    {
        return $this->state(fn (): array => [
            'due_date' => fake()->dateTimeBetween('-2 weeks', '-1 day'),
            'status' => fake()->randomElement([TaskStatus::Todo, TaskStatus::InProgress]),
        ]);
    }
}
