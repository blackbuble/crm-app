<?php
// app/Observers/CustomerObserver.php - Fixed
namespace App\Observers;

use App\Models\Customer;
use App\Models\User;
use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\NotificationHelper;
use App\Notifications\CustomerInactiveNotification;
use App\Notifications\CustomerConversionNotification;
use App\Notifications\CustomerReassignedNotification;
use App\Notifications\CustomerCreatedNotification;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class CustomerObserver
{
    public function creating(Customer $customer): void
    {
        $this->setDisplayName($customer);
    }

    public function updating(Customer $customer): void
    {
        $this->setDisplayName($customer);
    }

    protected function setDisplayName(Customer $customer): void
    {
        // Only override if the relevant fields are actually present/dirty
        if ($customer->type === 'personal') {
             $first = $customer->first_name ?: '';
             $last = $customer->last_name ?: '';
             if ($first || $last) {
                $customer->name = trim("$first $last");
             }
        } elseif ($customer->type === 'company') {
             if ($customer->company_name) {
                $customer->name = $customer->company_name;
             }
        }
        
        // Final fallback to prevent NULL
        if (empty($customer->name)) {
            $customer->name = 'Top Customer'; // Default fallback
        }
    }

    public function created(Customer $customer): void
    {
        \Log::info('CustomerObserver::created triggered', [
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'assigned_to' => $customer->assigned_to,
        ]);

        // Always notify about new customer creation
        $this->notifyNewCustomer($customer);
    }

    protected function notifyNewCustomer(Customer $customer): void
    {
        // For new customer: Send to assigned user + direct manager only
        if ($customer->assigned_to && $customer->assignedUser) {
            $assignedUser = $customer->assignedUser;
            
            // Notify assigned user
            $assignedUser->notify(new CustomerCreatedNotification($customer, 'assigned'));
            \Log::info('Notification sent to assigned user', ['user_id' => $assignedUser->id]);
            
            // Notify direct manager if exists
            if ($assignedUser->manager) {
                $assignedUser->manager->notify(new CustomerCreatedNotification($customer, 'manager'));
                \Log::info('Notification sent to direct manager', ['manager_id' => $assignedUser->manager->id]);
            }
        } else {
            // No assignment: Notify first super_admin
            $admin = User::role('super_admin')->first();
            if ($admin) {
                $admin->notify(new CustomerCreatedNotification($customer, 'manager'));
                \Log::info('Notification sent to super_admin (unassigned)', ['admin_id' => $admin->id]);
            }
        }
    }

    public function updated(Customer $customer): void
    {
        \Log::info('CustomerObserver::updated triggered', [
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'dirty_fields' => array_keys($customer->getDirty()),
            'status' => $customer->status,
            'old_status' => $customer->getOriginal('status'),
            'assigned_to' => $customer->assigned_to,
            'old_assigned_to' => $customer->getOriginal('assigned_to'),
        ]);

        // Check if status changed to inactive (not interested)
        if ($customer->isDirty('status') && $customer->status === 'inactive') {
            \Log::info('✅ Condition met: Status changed to inactive', ['customer_id' => $customer->id]);
            $this->notifyInactive($customer);
        } else {
            \Log::info('❌ Condition NOT met: Status inactive', [
                'isDirty' => $customer->isDirty('status'),
                'status' => $customer->status,
                'is_inactive' => $customer->status === 'inactive',
            ]);
        }
        
        // Check if status changed to customer (won)
        if ($customer->isDirty('status') && $customer->status === 'customer') {
            $oldStatus = $customer->getOriginal('status');
            if (in_array($oldStatus, ['lead', 'prospect'])) {
                \Log::info('✅ Condition met: Customer converted from lead/prospect', [
                    'customer_id' => $customer->id,
                    'old_status' => $oldStatus,
                ]);
                $this->notifyConversion($customer);
            } else {
                \Log::info('❌ Condition NOT met: Old status not lead/prospect', [
                    'old_status' => $oldStatus,
                ]);
            }
        } else {
            \Log::info('❌ Condition NOT met: Status customer', [
                'isDirty' => $customer->isDirty('status'),
                'status' => $customer->status,
                'is_customer' => $customer->status === 'customer',
            ]);
        }
        
        // Check if customer was reassigned
        if ($customer->isDirty('assigned_to')) {
            \Log::info('✅ Condition met: Customer reassigned', [
                'customer_id' => $customer->id,
                'old_user' => $customer->getOriginal('assigned_to'),
                'new_user' => $customer->assigned_to,
            ]);
            $this->notifyReassignment($customer);
        } else {
            \Log::info('❌ Condition NOT met: Reassignment', [
                'isDirty' => $customer->isDirty('assigned_to'),
            ]);
        }
    }

    protected function notifyInactive(Customer $customer): void
    {
        \Log::info('🔔 notifyInactive called', ['customer_id' => $customer->id]);
        
        $assignedUser = $customer->assignedUser;
        
        // For inactive: Send to ONE manager only (direct manager or first super_admin)
        $recipient = null;
        
        // Priority 1: Direct manager of assigned user
        if ($assignedUser && $assignedUser->manager) {
            $recipient = $assignedUser->manager;
            \Log::info('Sending to direct manager', [
                'manager_id' => $recipient->id,
                'manager_name' => $recipient->name,
            ]);
        } 
        // Priority 2: First super admin
        else {
            $recipient = User::role('super_admin')->first();
            \Log::info('Sending to first super_admin', [
                'admin_id' => $recipient?->id,
                'admin_name' => $recipient?->name,
            ]);
        }
        
        if ($recipient) {
            try {
                // Send Laravel native notification only
                $recipient->notify(new CustomerInactiveNotification($customer, $assignedUser?->name));
                
                \Log::info('✅ Notification sent successfully', [
                    'recipient_id' => $recipient->id,
                    'recipient_name' => $recipient->name,
                ]);
            } catch (\Exception $e) {
                \Log::error('❌ Failed to send notification', [
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            \Log::warning('⚠️ No recipient found for inactive notification');
        }
        
        \Log::info('🏁 notifyInactive completed');
    }

    protected function notifyConversion(Customer $customer): void
    {
        // For conversion: Send to assigned user + ALL managers (important event!)
        $assignedUser = $customer->assignedUser;
        
        // Notify assigned user (celebration!)
        if ($assignedUser) {
            $assignedUser->notify(new CustomerConversionNotification($customer, $assignedUser->name));
            \Log::info('🎉 Conversion notification sent to sales rep', ['user_id' => $assignedUser->id]);
        }
        
        // Notify ALL managers (this is important!)
        $managers = User::role(['super_admin', 'sales_manager'])->get();
        \Illuminate\Support\Facades\Notification::send(
            $managers,
            new CustomerConversionNotification($customer, $assignedUser?->name ?? 'Unknown')
        );
        \Log::info('🎉 Conversion notification sent to all managers', [
            'manager_count' => $managers->count(),
        ]);
    }

    protected function notifyReassignment(Customer $customer): void
    {
        // For reassignment: Send to old user, new user, and direct manager only
        $oldAssignedId = $customer->getOriginal('assigned_to');
        $newAssignedId = $customer->assigned_to;
        
        $oldAssignedUser = User::find($oldAssignedId);
        $newAssignedUser = User::find($newAssignedId);
        
        // Notify old assignee
        if ($oldAssignedUser) {
            $oldAssignedUser->notify(new CustomerReassignedNotification(
                $customer, 
                'old', 
                $oldAssignedUser->name, 
                $newAssignedUser?->name
            ));
            \Log::info('Reassignment notification sent to old user', ['user_id' => $oldAssignedUser->id]);
        }
        
        // Notify new assignee
        if ($newAssignedUser) {
            $newAssignedUser->notify(new CustomerReassignedNotification(
                $customer, 
                'new', 
                $oldAssignedUser?->name, 
                $newAssignedUser->name
            ));
            \Log::info('Reassignment notification sent to new user', ['user_id' => $newAssignedUser->id]);
        }
        
        // Notify direct manager of new assignee (if exists)
        if ($newAssignedUser && $newAssignedUser->manager) {
            $newAssignedUser->manager->notify(new CustomerReassignedNotification(
                $customer, 
                'manager', 
                $oldAssignedUser?->name, 
                $newAssignedUser->name
            ));
            \Log::info('Reassignment notification sent to manager', ['manager_id' => $newAssignedUser->manager->id]);
        }
    }
}