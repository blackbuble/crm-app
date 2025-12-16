<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CountryManagerRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Country Manager role if it doesn't exist
        $countryManagerRole = Role::firstOrCreate(['name' => 'country_manager']);

        // Get all permissions
        $permissions = Permission::all();

        // Country Manager should have most permissions except shield and super admin stuff
        $countryManagerPermissions = $permissions->filter(function ($permission) {
            // Exclude shield permissions (only for super admin)
            if (str_contains($permission->name, 'shield')) {
                return false;
            }
            
            // Exclude role and permission management (only for super admin)
            if (str_contains($permission->name, 'role') || str_contains($permission->name, 'permission')) {
                return false;
            }
            
            return true;
        });

        // Assign permissions to Country Manager
        $countryManagerRole->syncPermissions($countryManagerPermissions);

        $this->command->info('Country Manager role created/updated with permissions.');
    }
}
