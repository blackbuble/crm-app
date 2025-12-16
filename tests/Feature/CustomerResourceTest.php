<?php

namespace Tests\Feature;

use App\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CustomerResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permissions required for Filament Shield/Access
        Permission::create(['name' => 'view_any_customer', 'guard_name' => 'web']);
        Permission::create(['name' => 'create_customer', 'guard_name' => 'web']);
    }

    public function test_can_render_create_customer_page()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['view_any_customer', 'create_customer']);
        
        $this->actingAs($user)
            ->get(CreateCustomer::getUrl())
            ->assertSuccessful();
    }

    public function test_can_create_customer_via_form()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['view_any_customer', 'create_customer']);

        Livewire::actingAs($user)
            ->test(CreateCustomer::class)
            ->fillForm([
                'name' => 'Test Customer',
                'email' => 'test@example.com',
                'phone' => '08123456789',
                'type' => 'personal',
                'first_name' => 'John', // Required for Personal
                'last_name' => 'Doe',   // Required for Personal
                'status' => 'lead',
                'source' => 'Manual',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('customers', [
            'email' => 'test@example.com',
            'phone' => '08123456789',
        ]);
    }

    public function test_validation_errors_on_create()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['view_any_customer', 'create_customer']);

        Livewire::actingAs($user)
            ->test(CreateCustomer::class)
            ->fillForm([
                'type' => 'personal',
                'first_name' => '', // Required
            ])
            ->call('create')
            ->assertHasFormErrors(['first_name']);
    }
}
