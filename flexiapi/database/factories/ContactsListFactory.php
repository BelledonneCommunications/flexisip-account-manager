<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ContactsListFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => $this->faker->title,
            'description' => $this->faker->paragraph,
        ];
    }
}
