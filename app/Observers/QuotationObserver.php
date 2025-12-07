<?php
// app/Observers/QuotationObserver.php
namespace App\Observers;

use App\Models\Quotation;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class QuotationObserver
{
    public function created(Quotation $quotation): void
    {
        $this->notifyManagers(
            'New Quotation Created',
            "A new quotation {$quotation->quotation_number} has been created by {$quotation->user->name} for {$quotation->customer->name}",
            $quotation,
            'info'
        );
    }

    public function updated(Quotation $quotation): void
    {
        // Check if status changed
        if ($quotation->isDirty('status')) {
            $oldStatus = $quotation->getOriginal('status');
            $newStatus = $quotation->status;
            
            match($newStatus) {
                'accepted' => $this->notifyClosing($quotation),
                'rejected' => $this->notifyRejection($quotation),
                default => null,
            };
        }
    }

    protected function notifyClosing(Quotation $quotation): void
    {
        $this->notifyManagers(
            '🎉 Deal Closed!',
            "{$quotation->user->name} closed a deal with {$quotation->customer->name} worth Rp " . number_format($quotation->total, 0, ',', '.'),
            $quotation,
            'success'
        );
    }

    protected function notifyRejection(Quotation $quotation): void
    {
        $this->notifyManagers(
            '❌ Quotation Rejected',
            "Quotation {$quotation->quotation_number} for {$quotation->customer->name} was rejected. Created by {$quotation->user->name}",
            $quotation,
            'danger'
        );
    }

    protected function notifyManagers(string $title, string $body, Quotation $quotation, string $color = 'info'): void
    {
        // Get all managers and super admins
        $recipients = User::role(['super_admin', 'sales_manager'])->get();
        
        // Also notify the sales rep's direct manager if exists
        if ($quotation->user->manager_id) {
            $recipients = $recipients->push($quotation->user->manager);
        }
        
        $recipients = $recipients->unique('id');
        
        foreach ($recipients as $recipient) {
            Notification::make()
                ->title($title)
                ->body($body)
                ->icon($this->getIconForStatus($quotation->status))
                ->iconColor($color)
                ->actions([
                    Action::make('view')
                        ->label('View Quotation')
                        ->url(route('filament.admin.resources.quotations.edit', $quotation))
                        ->button(),
                    Action::make('view_customer')
                        ->label('View Customer')
                        ->url(route('filament.admin.resources.customers.edit', $quotation->customer)),
                ])
                ->sendToDatabase($recipient);
        }
    }

    protected function getIconForStatus(string $status): string
    {
        return match($status) {
            'accepted' => 'heroicon-o-check-circle',
            'rejected' => 'heroicon-o-x-circle',
            'sent' => 'heroicon-o-paper-airplane',
            default => 'heroicon-o-document-text',
        };
    }
}