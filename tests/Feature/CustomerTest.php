<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_be_created()
    {
        $user = User::factory()->create();
        
        $customer = Customer::factory()->create([
            'assigned_to' => $user->id,
            'name' => null, // Should trigger observer fallback
            'phone' => '081234567890',
        ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'phone' => '081234567890',
        ]);
        
        // Check Observer Logic (Name Fallback)
        $this->assertNotNull($customer->name);
        $this->assertEquals('Top Customer', $customer->name);
    }
    
    public function test_customer_display_name_logic_personal()
    {
        $customer = Customer::factory()->create([
            'type' => 'personal',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'Initial',
        ]);
        
        // Trigger save to fire observer
        $customer->save();
        
        $this->assertEquals('John Doe', $customer->fresh()->name);
    }

    public function test_customer_display_name_logic_company()
    {
        $customer = Customer::factory()->create([
            'type' => 'company',
            'company_name' => 'Acme Corp',
            'name' => 'Initial',
        ]);
        
        // Trigger save
        $customer->save();
        
        $this->assertEquals('Acme Corp', $customer->fresh()->name);
    }
}
