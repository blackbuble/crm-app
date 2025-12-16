<?php

namespace Tests\Feature;

use App\Filament\Resources\ExhibitionResource;
use App\Filament\Resources\UserResource;
use App\Filament\Pages\ManageStorage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'sales_manager', 'guard_name' => 'web']);
        Role::create(['name' => 'country_manager', 'guard_name' => 'web']);
        Role::create(['name' => 'sales_rep', 'guard_name' => 'web']);
    }

    public function test_exhibition_access()
    {
        // Admin - Allowed
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin)->get(ExhibitionResource::getUrl())->assertSuccessful();

        // Sales Manager - Allowed
        $manager = User::factory()->create();
        $manager->assignRole('sales_manager');
        $this->actingAs($manager)->get(ExhibitionResource::getUrl())->assertSuccessful();

        // Sales Rep - Denied
        $rep = User::factory()->create();
        $rep->assignRole('sales_rep');
        $this->actingAs($rep)->get(ExhibitionResource::getUrl())->assertForbidden();
    }

    public function test_storage_settings_access()
    {
        // Admin - Allowed
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin)->get(ManageStorage::getUrl())->assertSuccessful();

        // Sales Manager - Denied
        $manager = User::factory()->create();
        $manager->assignRole('sales_manager');
        $this->actingAs($manager)->get(ManageStorage::getUrl())->assertForbidden();
    }

    public function test_user_resource_access()
    {
         // Admin - Allowed
         $admin = User::factory()->create();
         $admin->assignRole('super_admin');
         $this->actingAs($admin)->get(UserResource::getUrl())->assertSuccessful();
 
         // Sales Manager - Allowed
         $manager = User::factory()->create();
         $manager->assignRole('sales_manager');
         $this->actingAs($manager)->get(UserResource::getUrl())->assertSuccessful();
 
         // Sales Rep - Denied
         $rep = User::factory()->create();
         $rep->assignRole('sales_rep');
         $this->actingAs($rep)->get(UserResource::getUrl())->assertForbidden();
    }
}
