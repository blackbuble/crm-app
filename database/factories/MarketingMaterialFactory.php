<?php

namespace Database\Factories;

use App\Models\MarketingMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarketingMaterialFactory extends Factory
{
    protected $model = MarketingMaterial::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement(['brochure', 'price_list']),
            'description' => $this->faker->sentence(),
            'file_path' => 'marketing-materials/test.pdf',
            'thumbnail_path' => null,
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
