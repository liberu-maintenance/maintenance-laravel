<?php

namespace Database\Factories;

use App\Models\WorkOrderComment;
use App\Models\WorkOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkOrderCommentFactory extends Factory
{
    protected $model = WorkOrderComment::class;

    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'user_id' => User::factory(),
            'comment' => fake()->paragraph(),
            'is_internal' => fake()->boolean(30),
        ];
    }

    public function internal(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_internal' => true,
        ]);
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_internal' => false,
        ]);
    }
}
