<?php

namespace Database\Factories;

use App\AccountFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFileFactory extends Factory
{
    protected $model = AccountFile::class;

    public function definition(): array
    {
        $contentType = $this->faker->randomElement(AccountFile::VOICEMAIL_CONTENTTYPES);
        $uploadedAt  = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'name'         => $this->faker->uuid() . ($contentType === 'audio/wav' ? '.wav' : '.opus'),
            'content_type' => $contentType,
            'size'         => $contentType === 'audio/wav'
                ? $this->faker->numberBetween(200_000, 3_000_000)
                : $this->faker->numberBetween(8_000, 200_000),
            'sip_from'     => $this->faker->email(),
            'uploaded_at'  => $uploadedAt,
            'created_at'   => $uploadedAt,
            'updated_at'   => $uploadedAt,
        ];
    }
}
