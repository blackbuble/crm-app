<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\FollowUp;
use App\Models\CustomerAssignment;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Action;

class UserOffboarding extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-minus';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $title = 'User Offboarding';
    protected static ?int $navigationSort = 2;
    
    protected static string $view = 'filament.pages.user-offboarding';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'country_manager']);
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transfer Configuration')
                    ->description('Reassign data from a resigning user to another user.')
                    ->schema([
                        Forms\Components\Select::make('from_user_id')
                            ->label('Resigning User (From)')
                            ->options(User::where('id', '!=', auth()->id())->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Reset to_user if it's the same
                                $set('to_user_id', null);
                            }),

                        Forms\Components\Select::make('to_user_id')
                            ->label('Target User (To)')
                            ->options(function (Forms\Get $get) {
                                $fromId = $get('from_user_id');
                                return User::where('id', '!=', $fromId)
                                    ->where('is_active', true) // Assuming active flag or just all
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->different('from_user_id'),
                    ])->columns(2),

                Forms\Components\Section::make('Data to Transfer')
                    ->schema([
                        Forms\Components\Checkbox::make('transfer_customers')
                            ->label('Transfer Assigned Customers')
                            ->default(true)
                            ->helperText(fn (Forms\Get $get) => 'Total: ' . Customer::where('assigned_to', $get('from_user_id'))->count()),

                        Forms\Components\Checkbox::make('transfer_quotations')
                            ->label('Transfer Quotations Ownership')
                            ->default(true)
                            ->helperText(fn (Forms\Get $get) => 'Total: ' . Quotation::where('user_id', $get('from_user_id'))->count()),
                        
                        Forms\Components\Checkbox::make('transfer_followups')
                            ->label('Transfer Pending Follow-ups')
                            ->default(true)
                            ->helperText(fn (Forms\Get $get) => 'Total: ' . FollowUp::where('user_id', $get('from_user_id'))->where('status', 'pending')->count()),

                        Forms\Components\Checkbox::make('transfer_team')
                            ->label('Transfer Subordinates (Team Members)')
                            ->default(true)
                            ->visible(fn (Forms\Get $get) => User::where('manager_id', $get('from_user_id'))->exists())
                            ->helperText(fn (Forms\Get $get) => 'Team Size: ' . User::where('manager_id', $get('from_user_id'))->count()),
                    ])
                    ->columns(2)
                    ->visible(fn (Forms\Get $get) => $get('from_user_id')),

                Forms\Components\Section::make('Actions')
                    ->schema([
                        Forms\Components\Toggle::make('deactivate_user')
                            ->label('Deactivate Source User')
                            ->helperText('Prevent the user from logging in after transfer.')
                            ->default(true),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('from_user_id')),
            ])
            ->statePath('data');
    }

    public function transfer(): void
    {
        $data = $this->form->getState();
        
        $fromId = $data['from_user_id'];
        $toId = $data['to_user_id'];
        $fromUser = User::find($fromId);
        $toUser = User::find($toId);

        if (!$fromUser || !$toUser) {
            Notification::make()->title('Invalid users selected.')->danger()->send();
            return;
        }

        DB::transaction(function () use ($data, $fromId, $toId, $fromUser) {
            $stats = [];

            // 1. Transfer Customers
            if ($data['transfer_customers'] ?? false) {
                $count = Customer::where('assigned_to', $fromId)->count();
                if ($count > 0) {
                    // Log assignments? optionally
                    Customer::where('assigned_to', $fromId)->update(['assigned_to' => $toId]);
                    
                    // Ideally we should log CustomerAssignment for each, but bulk update is faster.
                    // If strict auditing is required, we would loop. For now, bulk update.
                    $stats[] = "$count Customers re-assigned";
                }
            }

            // 2. Transfer Quotations
            if ($data['transfer_quotations'] ?? false) {
                $count = Quotation::where('user_id', $fromId)->update(['user_id' => $toId]);
                if ($count > 0) $stats[] = "$count Quotations transferred";
            }

            // 3. Transfer Follow-ups
            if ($data['transfer_followups'] ?? false) {
                $count = FollowUp::where('user_id', $fromId)->update(['user_id' => $toId]);
                if ($count > 0) $stats[] = "$count Follow-ups transferred";
            }

            // 4. Transfer Team (Manager)
            if ($data['transfer_team'] ?? false) {
                $count = User::where('manager_id', $fromId)->update(['manager_id' => $toId]);
                if ($count > 0) $stats[] = "$count Team members re-assigned";
            }

            // 5. Deactivate
            if ($data['deactivate_user'] ?? false) {
                $fromUser->update(['is_active' => false]);
                $fromUser->syncRoles([]); // Remove all roles
                $stats[] = "User deactivated and roles removed";
            }

            Notification::make()
                ->title('Transfer Successful')
                ->success()
                ->body(implode("\n", $stats))
                ->send();
        });

        // Reset form
        $this->form->fill();
    }
}
