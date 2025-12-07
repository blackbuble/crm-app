<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class CustomerObserver
{
    public function updated(Customer $customer): void
    {
        // Check if status changed to inactive (not interested)
        if ($customer->isDirty('status') && $customer->status === 'inactive') {
            $this->notifyInactive($customer);
        }
        
        // Check if status changed to customer (won)
        if ($customer->isDirty('status') && $customer->status === 'customer') {
            $oldStatus = $customer->getOriginal('status');
            if (in_array($oldStatus, ['lead', 'prospect'])) {
                $this->notifyConversion($customer);
            }
        }
    }

    protected function notifyInactive(Customer $customer): void
    {
        $assignedUser = $customer->assignedUser;
        
        // Get all managers and super admins
        $recipients = User::role(['super_admin', 'sales_manager'])->get();
        
        // Also notify the sales rep's direct manager
        if ($assignedUser && $assignedUser->manager_id) {
            $recipients = $recipients->push($assignedUser->manager);
        }
        
        $recipients = $recipients->unique('id');
        
        foreach ($recipients as $recipient) {
            Notification::make()
                ->title('Customer Not Interested')
                ->body("{$customer->name} has been marked as inactive by " . ($assignedUser ? $assignedUser->name : 'Unknown'))
                ->icon('heroicon-o-exclamation-triangle')
                ->iconColor('warning')
                ->actions([
                    Action::make('view')
                        ->label('View Customer')
                        ->url(route('filament.admin.resources.customers.edit', $customer))
                        ->button(),
                ])
                ->sendToDatabase($recipient);
        }
    }

    protected function notifyConversion(Customer $customer): void
    {
        $assignedUser = $customer->assignedUser;
        
        $recipients = User::role(['super_admin', 'sales_manager'])->get();
        
        if ($assignedUser && $assignedUser->manager_id) {
            $recipients = $recipients->push($assignedUser->manager);
        }
        
        $recipients = $recipients->unique('id');
        
        foreach ($recipients as $recipient) {
            Notification::make()
                ->title('🎊 New Customer Conversion!')
                ->body("{$customer->name} has been converted to customer by " . ($assignedUser ? $assignedUser->name : 'Unknown'))
                ->icon('heroicon-o-trophy')
                ->iconColor('success')
                ->actions([
                    Action::make('view')
                        ->label('View Customer')
                        ->url(route('filament.admin.resources.customers.edit', $customer))
                        ->button(),
                ])
                ->sendToDatabase($recipient);
        }
    }
}
