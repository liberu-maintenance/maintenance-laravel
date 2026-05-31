<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'contact_id'   => fake()->unique()->numberBetween(1, 99999),
            'name'         => fake()->firstName(),
            'last_name'    => fake()->lastName(),
            'email'        => fake()->unique()->safeEmail(),
            'phone_number' => fake()->phoneNumber(),
        ];
    }
}
