<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Opportunity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    protected $model = Opportunity::class;

    public function definition(): array
    {
        return [
            'deal_size'    => $this->faker->randomFloat(2, 1000, 500000),
            'stage'        => $this->faker->randomElement(['prospecting', 'qualification', 'proposal', 'negotiation', 'closed_won', 'closed_lost']),
            'closing_date' => $this->faker->dateTimeBetween('now', '+6 months'),
            'team_id'      => null,
        ];
    }
}
