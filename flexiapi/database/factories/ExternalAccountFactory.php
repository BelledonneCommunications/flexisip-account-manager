<?php

namespace Database\Factories;

use App\ExternalAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExternalAccountFactory extends Factory
{
    protected $model = ExternalAccount::class;

    public function definition()
    {
        $username = $this->faker->username;
        $realm = config('app.realm') ?? config('app.sip_domain');

        return [
            'username' => $username,
            'domain' => config('app.sip_domain'),
            'group' => 'test',
            'password'   => hash('sha256', $username.':'.$realm.':testtest'),
            'algorithm'  => 'SHA-256',
        ];
    }
}
