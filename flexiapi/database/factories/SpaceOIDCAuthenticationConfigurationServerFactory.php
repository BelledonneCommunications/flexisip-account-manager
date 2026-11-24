<?php

namespace Database\Factories;

use App\SpaceOIDCAuthenticationConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpaceOIDCAuthenticationConfigurationFactory extends Factory
{
    protected $model = SpaceOIDCAuthenticationConfiguration::class;

    public function definition(): array
    {
        return [
            'authorization_server' => fake()->url(),
            'audience' => 'aud',
            'sip_id_claim' => 'sip_identity',
            'client_secret' => fake()->uuid(),
            'client_id' => 'fam',
            'space_id' => 1,
        ];
    }

    public function withSpaceId(int $spaceId): static
    {
        return $this->state(fn (array $attributes) => [
            'space_id' => $spaceId,
        ]);
    }
}
