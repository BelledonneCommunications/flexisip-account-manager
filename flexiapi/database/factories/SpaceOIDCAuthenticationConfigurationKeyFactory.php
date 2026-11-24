<?php

namespace Database\Factories;

use App\SpaceOIDCAuthenticationConfigurationKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpaceOIDCAuthenticationConfigurationKey>
 */
class SpaceOIDCAuthenticationConfigurationKeyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kid' => 'kid',
            'public_key' => 'public_key',
        ];
    }
}
