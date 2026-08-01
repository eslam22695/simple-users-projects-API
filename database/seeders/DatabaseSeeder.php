<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Project::factory()
            ->count(5)
            ->for($user)
            ->create()
            ->each(function (Project $project): void {
                Task::factory()->count(8)->for($project)->create();
                Task::factory()->count(2)->overdue()->for($project)->create();
            });
    }
}