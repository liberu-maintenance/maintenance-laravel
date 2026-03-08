<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip' => fake()->postcode(),
            'phone_number' => fake()->phoneNumber(),
            'website' => fake()->url(),
            'industry' => fake()->randomElement(['Manufacturing', 'Healthcare', 'Technology', 'Construction', 'Retail']),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(['customer', 'vendor', 'supplier', 'both']),
            'email' => fake()->safeEmail(),
            'is_active' => true,
        ];
    }
}
