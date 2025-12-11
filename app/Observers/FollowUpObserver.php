<?php
// app/Observers/FollowUpObserver.php
namespace App\Observers;

use App\Models\FollowUp;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class FollowUpObserver
{
    public function created(FollowUp $followUp): void
    {
        // Notify assigned user about new follow-up
        Notification::make()
            ->title('New Follow-up Created')
            ->body("A new {$followUp->type} follow-up has been scheduled for {$followUp->customer->name} on {$followUp->follow_up_date->format('M d, Y')}")
            ->icon('heroicon-o-clock')
            ->iconColor('info')
            ->actions([
                Action::make('view')
                    ->label('View Follow-up')
                    ->url(route('filament.admin.resources.customers.edit', [
                        'record' => $followUp->customer_id,
                    ]))
                    ->button(),
            ])
            ->sendToDatabase($followUp->user);
    }

    public function updated(FollowUp $followUp): void
    {
        // Check if status changed to completed
        if ($followUp->isDirty('status') && $followUp->status === 'completed') {
            $this->notifyCompletion($followUp);
        }
    }

    protected function notifyCompletion(FollowUp $followUp): void
    {
        // Notify the user
        Notification::make()
            ->title('Follow-up Completed')
            ->body("Follow-up for {$followUp->customer->name} has been marked as completed")
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->sendToDatabase($followUp->user);
        
        // Notify managers if it's an important follow-up
        $managers = User::role(['super_admin', 'sales_manager'])->get();
        
        if ($followUp->user->manager_id) {
            $managers = $managers->push($followUp->user->manager);
        }
        
        $managers = $managers->unique('id');
        
        foreach ($managers as $manager) {
            Notification::make()
                ->title('Follow-up Completed by Team Member')
                ->body("{$followUp->user->name} completed a follow-up with {$followUp->customer->name}")
                ->icon('heroicon-o-check-circle')
                ->iconColor('info')
                ->actions([
                    Action::make('view')
                        ->label('View Customer')
                        ->url(route('filament.admin.resources.customers.edit', $followUp->customer_id))
                        ->button(),
                ])
                ->sendToDatabase($manager);
        }
    }
}