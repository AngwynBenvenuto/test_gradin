<?php

namespace Database\Factories;

use App\Models\Courier;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Courier> */
class CourierFactory extends Factory
{
    protected $model = Courier::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('CR-######'),
            'name' => $this->faker->name(),
            'phone' => $this->faker->unique()->numerify('08##########'),
            'email' => $this->faker->unique()->safeEmail(),
            'level' => $this->faker->numberBetween(1, 5),
            'is_active' => true,
            'vehicle_type' => 'motorcycle',
            'registered_at' => now(),
        ];
    }
}
