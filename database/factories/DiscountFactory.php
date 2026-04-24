<?php

namespace Database\Factories;

use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Discount>
 */
class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['percentage', 'fixed']);
        $value = $type === 'percentage'
            ? $this->faker->numberBetween(5, 50)
            : $this->faker->randomFloat(2, 1, 100);

        return [
            'title' => $this->faker->unique()->words(2, true),
            'type' => $type,
            'value' => $value,
        ];
    }
}

