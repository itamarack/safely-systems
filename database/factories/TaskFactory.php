<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        $status = $this->faker->randomElement(TaskStatus::cases());

        return [
            'title'             => $this->faker->sentence(4),
            'description'       => $this->faker->optional()->paragraph(),
            'due_date'          => $this->faker->dateTimeBetween('-2 weeks', '+3 weeks'),
            'user_id'           => User::factory(),
            'priority'          => $this->faker->randomElement(TaskPriority::cases()),
            'status'            => $status,
            'corrective_action' => $status === TaskStatus::NonCompliant ? $this->faker->sentence() : null,
        ];
    }
}
