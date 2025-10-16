<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'company' => $this->faker->company(),
            'title' => $this->faker->jobTitle(),
            'source' => $this->faker->randomElement(['Web Form', 'Email', 'LinkedIn', 'Referral', 'Cold Call']),
            'priority' => $this->faker->randomElement(['Hot', 'Warm', 'Cold']),
            'notes' => $this->faker->paragraph(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'industry' => $this->faker->randomElement(['Technology', 'Finance', 'Healthcare', 'Manufacturing', 'Retail']),
            'company_size' => $this->faker->randomElement(['Small', 'Medium', 'Large']),
            'score' => $this->faker->numberBetween(0, 100),
            'last_contact_date' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
            'created_by_user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the lead is hot priority.
     */
    public function hot(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'Hot',
            'score' => $this->faker->numberBetween(70, 100),
        ]);
    }

    /**
     * Indicate that the lead is warm priority.
     */
    public function warm(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'Warm',
            'score' => $this->faker->numberBetween(40, 69),
        ]);
    }

    /**
     * Indicate that the lead is cold priority.
     */
    public function cold(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'Cold',
            'score' => $this->faker->numberBetween(0, 39),
        ]);
    }
}
