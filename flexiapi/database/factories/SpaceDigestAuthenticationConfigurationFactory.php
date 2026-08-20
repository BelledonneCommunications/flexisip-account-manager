<?php

namespace Database\Factories;

use App\PasswordAlgorithm;
use App\SpaceDigestAuthenticationConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpaceDigestAuthenticationConfigurationFactory extends Factory
{
    protected $model = SpaceDigestAuthenticationConfiguration::class;

    public function definition(): array
    {
        return [
            'realm' => fake()->domainName(),
            'default_password_algorithm' => PasswordAlgorithm::SHA256,
        ];
    }

    public function withSpaceId(int $spaceId): static
    {
        return $this->state(fn (array $attributes) => [
            'space_id' => $spaceId,
        ]);
    }

    public function withRealm(string $realm)
    {
        return $this->state(fn (array $attributes) => [
            'realm' => $realm,
        ]);
    }
}
