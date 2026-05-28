<?php

namespace Database\Factories;

use App\Wizard;
use App\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class WizardFactory extends Factory
{
    protected $model = Wizard::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'provisioning_account_id' => Account::factory(),
            'sip' => 'sip:' . $this->faker->email,
            'linphone_action' => 'call',
            'linphone_use_sips' => true,
            'used_at' => null,
        ];
    }
}
