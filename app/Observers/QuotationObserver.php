<?php
// app/Observers/QuotationObserver.php - Fixed
namespace App\Observers;

use App\Models\Quotation;
use App\Models\User;
use App\Filament\Resources\QuotationResource;
use App\Filament\Resources\CustomerResource;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class QuotationObserver
{
    public function creating(Quotation $quotation): void
    {
        // Auto-generate quotation number if not provided
        if (empty($quotation->quotation_number)) {
            $quotation->quotation_number = Quotation::generateQuotationNumber();
        }

        // Auto-set user_id if not provided
        if (empty($quotation->user_id) && auth()->check()) {
            $quotation->user_id = auth()->id();
        }
    }

    public function created(Quotation $quotation): void
    {
        // Notify sales rep
        Notification::make()
            ->title('Quotation Created')
            ->body("Quotation {$quotation->quotation_number} for {$quotation->customer->name} has been created")
            ->icon('heroicon-o-document-text')
            ->iconColor('info')
            ->actions([
                Action::make('view')
                    ->label('View Quotation')
                    ->url(route('filament.admin.resources.quotations.edit', $quotation))
                    ->button(),
            ])
            ->sendToDatabase($quotation->user);
        
        // Notify managers
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
                'sent' => $this->notifySent($quotation),
                'accepted' => $this->notifyClosing($quotation),
                'rejected' => $this->notifyRejection($quotation),
                default => null,
            };
        }
    }

    protected function notifySent(Quotation $quotation): void
    {
        // Notify sales rep
        Notification::make()
            ->title('Quotation Sent')
            ->body("Quotation {$quotation->quotation_number} has been sent to {$quotation->customer->name}")
            ->icon('heroicon-o-paper-airplane')
            ->iconColor('info')
            ->actions([
                Action::make('view')
                    ->label('View Quotation')
                    ->url(route('filament.admin.resources.quotations.edit', $quotation))
                    ->button(),
            ])
            ->sendToDatabase($quotation->user);
    }

    protected function notifyClosing(Quotation $quotation): void
    {
        // Notify sales rep with celebration
        Notification::make()
            ->title('🎉 Deal Closed!')
            ->body("Congratulations! Quotation {$quotation->quotation_number} for " . format_currency($quotation->total) . " has been accepted!")
            ->icon('heroicon-o-trophy')
            ->iconColor('success')
            ->actions([
                Action::make('view')
                    ->label('View Quotation')
                    ->url(route('filament.admin.resources.quotations.edit', $quotation))
                    ->button(),
            ])
            ->sendToDatabase($quotation->user)
            ->broadcast([$quotation->user]);
        
        // Notify managers
        $this->notifyManagers(
            '🎉 Deal Closed!',
            "{$quotation->user->name} closed a deal with {$quotation->customer->name} worth " . format_currency($quotation->total),
            $quotation,
            'success'
        );
    }

    protected function notifyRejection(Quotation $quotation): void
    {
        // Notify sales rep
        Notification::make()
            ->title('Quotation Rejected')
            ->body("Quotation {$quotation->quotation_number} for {$quotation->customer->name} was rejected")
            ->icon('heroicon-o-x-circle')
            ->iconColor('danger')
            ->actions([
                Action::make('view')
                    ->label('View Quotation')
                    ->url(route('filament.admin.resources.quotations.edit', $quotation))
                    ->button(),
                Action::make('create_followup')
                    ->label('Create Follow-up')
                    ->url(route('filament.admin.resources.follow-ups.create', [
                        'customer_id' => $quotation->customer_id
                    ]))
                    ->button(),
            ])
            ->sendToDatabase($quotation->user);
        
        // Notify managers
        $this->notifyManagers(
            'Quotation Rejected',
            "Quotation {$quotation->quotation_number} for {$quotation->customer->name} was rejected. Created by {$quotation->user->name}",
            $quotation,
            'warning'
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