<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Awobaz\Compoships\Database\Eloquent\Factories\ComposhipsFactory;

class StatisticsMessageFactory extends Factory
{
    use ComposhipsFactory;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'from_username' => $this->faker->userName(),
            'from_domain' => $this->faker->domainName(),
            'sent_at' => $this->faker->dateTimeBetween('-1 year'),
            'encrypted' => false
        ];
    }
}
