<?php

namespace Database\Factories;

use App\Models\Quotation;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    public function definition(): array
    {
        return [
            'quotation_number' => 'Q-' . $this->faker->unique()->numberBetween(1000, 9999),
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'quotation_date' => now(),
            'valid_until' => now()->addDays(30),
            'status' => 'draft',
            'subtotal' => 1000000,
            'tax_amount' => 110000,
            'total' => 1110000,
            'items' => [
                [
                    'description' => 'Test Item',
                    'quantity' => 1,
                    'unit_price' => 1000000,
                    'total' => 1000000
                ]
            ],
        ];
    }
}
