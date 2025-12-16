<?php

namespace Database\Factories;

use App\Models\Exhibition;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExhibitionFactory extends Factory
{
    protected $model = Exhibition::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3) . ' Expo',
            'start_date' => now(),
            'end_date' => now()->addDays(3),
            'location' => $this->faker->city(),
            'description' => $this->faker->paragraph(),
            'booth_cost' => 5000000,
            'operational_cost' => 1000000,
        ];
    }
}
