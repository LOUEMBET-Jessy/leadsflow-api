<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'status' => $this->faker->randomElement(['todo', 'in_progress', 'completed']),
            'assigned_to_user_id' => User::factory(),
            'created_by_user_id' => User::factory(),
            'lead_id' => $this->faker->optional()->randomElement(Lead::pluck('id')),
            'completion_date' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
            'reminders' => $this->faker->optional()->randomElements([
                now()->addDays(1)->toISOString(),
                now()->addDays(3)->toISOString(),
                now()->addDays(7)->toISOString(),
            ], $this->faker->numberBetween(0, 2)),
        ];
    }

    /**
     * Indicate that the task is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completion_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the task is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
            'status' => $this->faker->randomElement(['todo', 'in_progress']),
        ]);
    }

    /**
     * Indicate that the task is due today.
     */
    public function dueToday(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => now()->setTime(17, 0), // 5 PM today
            'status' => $this->faker->randomElement(['todo', 'in_progress']),
        ]);
    }
}
