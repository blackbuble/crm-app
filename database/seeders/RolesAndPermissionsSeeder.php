<?php
// database/seeders/RolesAndPermissionsSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for each resource
        $permissions = [
            // Customer permissions
            'view_any_customer',
            'view_customer',
            'create_customer',
            'update_customer',
            'delete_customer',
            'delete_any_customer',
            'force_delete_customer',
            'force_delete_any_customer',
            'restore_customer',
            'restore_any_customer',
            'replicate_customer',
            'reorder_customer',
            
            // Quotation permissions
            'view_any_quotation',
            'view_quotation',
            'create_quotation',
            'update_quotation',
            'delete_quotation',
            'delete_any_quotation',
            'force_delete_quotation',
            'force_delete_any_quotation',
            'restore_quotation',
            'restore_any_quotation',
            'replicate_quotation',
            'reorder_quotation',
            
            // Follow-up permissions
            'view_any_follow::up',
            'view_follow::up',
            'create_follow::up',
            'update_follow::up',
            'delete_follow::up',
            'delete_any_follow::up',
            
            // KPI Target permissions
            'view_any_kpi::target',
            'view_kpi::target',
            'create_kpi::target',
            'update_kpi::target',
            'delete_kpi::target',
            'delete_any_kpi::target',
            
            // Reports
            'view_reports',
            'export_reports',
            
            // Settings
            'view_settings',
            'update_settings',
            
            // Role management
            'view_any_role',
            'view_role',
            'create_role',
            'update_role',
            'delete_role',
            'delete_any_role',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        
        // Super Admin - has all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Sales Manager - can manage team and view everything
        $salesManager = Role::firstOrCreate(['name' => 'sales_manager']);
        $salesManager->givePermissionTo([
            // Customers - full access
            'view_any_customer',
            'view_customer',
            'create_customer',
            'update_customer',
            'delete_customer',
            'delete_any_customer',
            
            // Quotations - full access
            'view_any_quotation',
            'view_quotation',
            'create_quotation',
            'update_quotation',
            'delete_quotation',
            'delete_any_quotation',
            
            // Follow-ups - full access
            'view_any_follow::up',
            'view_follow::up',
            'create_follow::up',
            'update_follow::up',
            'delete_follow::up',
            'delete_any_follow::up',
            
            // KPI Targets - full access
            'view_any_kpi::target',
            'view_kpi::target',
            'create_kpi::target',
            'update_kpi::target',
            'delete_kpi::target',
            'delete_any_kpi::target',
            
            // Reports
            'view_reports',
            'export_reports',
        ]);

        // Sales Rep - limited access to their own data
        $salesRep = Role::firstOrCreate(['name' => 'sales_rep']);
        $salesRep->givePermissionTo([
            // Customers - can only view/update their assigned customers
            'view_any_customer', // filtered by assigned_to
            'view_customer',
            'create_customer',
            'update_customer',
            
            // Quotations - can manage their own
            'view_any_quotation', // filtered by user_id
            'view_quotation',
            'create_quotation',
            'update_quotation',
            
            // Follow-ups - can manage their own
            'view_any_follow::up', // filtered by user_id
            'view_follow::up',
            'create_follow::up',
            'update_follow::up',
            
            // KPI Targets - can only view their own
            'view_any_kpi::target',
            'view_kpi::target',
        ]);
    }
}

// Run this command after creating the seeder:
// php artisan db:seed --class=RolesAndPermissionsSeeder