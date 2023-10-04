<?php

namespace Database\Factories;

use App\StatisticsMessage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Awobaz\Compoships\Database\Eloquent\Factories\ComposhipsFactory;

class StatisticsMessageDeviceFactory extends Factory
{
    use ComposhipsFactory;

    public function definition(): array
    {
        $message = StatisticsMessage::factory()->create();

        return [
            'message_id' => $message->id,
            'to_username' => $this->faker->userName(),
            'to_domain' => $this->faker->domainName(),
            'device_id' => $this->faker->uuid(),
            'received_at' => $this->faker->dateTimeBetween('-1 year'),
            'last_status' => 200,
        ];
    }
}
