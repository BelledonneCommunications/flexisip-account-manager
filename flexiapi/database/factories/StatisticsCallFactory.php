<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StatisticsCallFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'from_username' => $this->faker->userName(),
            'from_domain' => $this->faker->domainName(),
            'to_username' => $this->faker->userName(),
            'to_domain' => $this->faker->domainName(),
            'initiated_at' => $this->faker->dateTimeBetween('-1 year'),
            'ended_at' => $this->faker->dateTimeBetween('-1 year'),
        ];
    }
}
