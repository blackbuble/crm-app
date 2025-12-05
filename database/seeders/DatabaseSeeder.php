<?php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use App\Models\FollowUp;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // First create basic permissions if they don't exist
        $permissions = ['view_any_customer', 'view_customer', 'create_customer', 'update_customer', 'delete_customer'];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $salesManager = Role::firstOrCreate(['name' => 'sales_manager']);
        $salesRep = Role::firstOrCreate(['name' => 'sales_rep']);

        // Create super admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@crm.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole($superAdmin);

        // Create sales manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@crm.com'],
            [
                'name' => 'Sales Manager',
                'password' => bcrypt('password'),
            ]
        );
        $manager->assignRole($salesManager);

        // Create sales rep
        $salesUser = User::firstOrCreate(
            ['email' => 'sales@crm.com'],
            [
                'name' => 'Sales Representative',
                'password' => bcrypt('password'),
            ]
        );
        $salesUser->assignRole($salesRep);

        // Create sample customers
        $customers = [
            [
                'type' => 'company',
                'company_name' => 'Tech Solutions Inc',
                'name' => 'Tech Solutions Inc',
                'email' => 'contact@techsolutions.com',
                'phone' => '+1234567890',
                'address' => '123 Tech Street, Silicon Valley, CA',
                'tax_id' => 'TAX123456',
                'status' => 'lead',
            ],
            [
                'type' => 'personal',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'name' => 'John Doe',
                'email' => 'john.doe@email.com',
                'phone' => '+1987654321',
                'address' => '456 Main Street, New York, NY',
                'status' => 'prospect',
            ],
            [
                'type' => 'company',
                'company_name' => 'Global Enterprises',
                'name' => 'Global Enterprises',
                'email' => 'info@globalent.com',
                'phone' => '+1122334455',
                'address' => '789 Business Ave, London, UK',
                'tax_id' => 'TAX789012',
                'status' => 'customer',
            ],
            [
                'type' => 'personal',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'name' => 'Jane Smith',
                'email' => 'jane.smith@email.com',
                'phone' => '+1555666777',
                'address' => '321 Oak Road, Toronto, CA',
                'status' => 'lead',
            ],
        ];

        foreach ($customers as $customerData) {
            $customer = Customer::create($customerData);
            
            // Add some tags
            $customer->attachTags(['Important', 'High Value']);

            // Create follow-ups
            FollowUp::create([
                'customer_id' => $customer->id,
                'user_id' => $admin->id,
                'type' => 'whatsapp',
                'follow_up_date' => now()->addDays(rand(1, 7)),
                'notes' => 'Initial contact follow-up',
                'status' => 'pending',
            ]);

            FollowUp::create([
                'customer_id' => $customer->id,
                'user_id' => $manager->id,
                'type' => 'email',
                'follow_up_date' => now()->addDays(rand(8, 14)),
                'notes' => 'Send proposal',
                'status' => 'pending',
            ]);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@crm.com / password');
        $this->command->info('Manager: manager@crm.com / password');
        $this->command->info('Sales: sales@crm.com / password');
    }
}