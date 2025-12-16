<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use App\Models\Exhibition; // Assuming we will create this factory too
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'type' => 'personal',
            'status' => 'lead',
            'source' => 'Manual',
            'assigned_to' => User::factory(), // Creates a user if not provided
        ];
    }
}
