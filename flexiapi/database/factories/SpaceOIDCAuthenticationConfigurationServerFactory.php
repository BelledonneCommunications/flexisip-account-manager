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
            'server_url' => fake()->url(),
            'realm' => 'Linphone',
            'public_key' => fake()->sha256(),
            'sip_identifier' => 'sip_identity',
            'client_secret' => fake()->uuid(),
            'client_id' => 'fam',
            'role_provisioning' => fake()->name(),
            'auto_provisioning' => false,
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
