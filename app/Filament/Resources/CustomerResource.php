<?php
// app/Filament/Resources/CustomerResource.php - Updated
namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\CustomerAssignment;
use App\Models\User;
use App\Imports\CustomersImport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\CustomersExport;
use App\Exports\CustomersTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Sales Operations';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn () => static::getEloquentQuery())
            ->columns(static::getTableColumns())
            ->filters(static::getTableFilters())
            ->actions(static::getTableActions())
            ->bulkActions(static::getTableBulkActions())
            ->headerActions(static::getTableHeaderActions());
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        
        // Sales rep can only see their assigned customers
        if (!$user->hasAnyRole(['super_admin', 'sales_manager'])) {
            $query->where('assigned_to', $user->id);
        }
        
        return $query;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FollowUpsRelationManager::class,
            RelationManagers\QuotationsRelationManager::class,
            RelationManagers\AssignmentHistoryRelationManager::class,
        ];
    }

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Customer Type')
                ->schema([
                    Forms\Components\Radio::make('type')
                        ->options([
                            'personal' => 'Personal',
                            'company' => 'Company',
                        ])
                        ->required()
                        ->live()
                        ->default('personal'),
                ]),

            Forms\Components\Section::make('Personal Information')
                ->schema([
                    Forms\Components\TextInput::make('first_name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('last_name')
                        ->required()
                        ->maxLength(255),
                ])
                ->visible(fn (Forms\Get $get) => $get('type') === 'personal')
                ->columns(2),

            Forms\Components\Section::make('Company Information')
                ->schema([
                    Forms\Components\TextInput::make('company_name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('tax_id')
                        ->label('Tax ID / VAT Number')
                        ->maxLength(255),
                ])
                ->visible(fn (Forms\Get $get) => $get('type') === 'company')
                ->columns(2),


            Forms\Components\Section::make('Contact Information')
                ->schema([
                    Forms\Components\Select::make('country')
                        ->label('Country')
                        ->options(get_countries())
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            // Auto-set country code based on country using helper function
                            $dialCode = get_dial_code_by_country($state);
                            if ($dialCode) {
                                $set('country_code', $dialCode);
                            }
                        }),
                    
                    Forms\Components\Select::make('country_code')
                        ->label('Country Code')
                        ->options(get_country_codes())
                        ->searchable()
                        ->helperText('Phone country code for WhatsApp'),
                    
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->maxLength(255),
                    
                    Forms\Components\TextInput::make('phone')
                        ->tel()
                        ->maxLength(255)
                        ->helperText('Enter phone number without country code'),
                    
                    Forms\Components\Textarea::make('address')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Additional Information')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'lead' => 'Lead',
                            'prospect' => 'Prospect',
                            'customer' => 'Customer',
                            'inactive' => 'Inactive',
                        ])
                        ->required()
                        ->default('lead'),
                    
                    Forms\Components\Select::make('source')
                        ->label('Acquisition Source')
                        ->options([
                            'Meta Ads' => 'Meta Ads (FB/IG)',
                            'Google Ads' => 'Google Ads',
                            'TikTok Ads' => 'TikTok Ads',
                            'LinkedIn Ads' => 'LinkedIn Ads',
                            'Twitter Ads' => 'Twitter/X Ads',
                            'Organic' => 'Organic',
                            'Referral' => 'Referral',
                            'Other' => 'Other',
                        ])
                        ->searchable(),
                    
                    Forms\Components\Select::make('assigned_to')
                        ->label('Assign To')
                        ->relationship('assignedUser', 'name')
                        ->searchable()
                        ->preload()
                        ->default(fn () => auth()->id())
                        ->helperText('Sales person responsible for this customer')
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            $set('assigned_at', now());
                        }),
                    
                    Forms\Components\Select::make('exhibition_id')
                        ->label('Exhibition Source')
                        ->relationship('exhibition', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('If obtained from an event'),

                    Forms\Components\SpatieTagsInput::make('tags'),
                    
                    Forms\Components\Textarea::make('notes')
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Marketing Data')
                ->schema([
                    Forms\Components\TextInput::make('utm_source')
                        ->label('Source (UTM)')
                        ->disabled(),
                    Forms\Components\TextInput::make('utm_medium')
                        ->label('Medium (UTM)')
                        ->disabled(),
                    Forms\Components\TextInput::make('utm_campaign')
                        ->label('Campaign (UTM)')
                        ->disabled(),
                    Forms\Components\TextInput::make('utm_content')
                        ->label('Content (UTM)')
                        ->disabled(),
                    Forms\Components\TextInput::make('utm_term')
                        ->label('Term (UTM)')
                        ->disabled(),
                    Forms\Components\TextInput::make('gclid')
                        ->label('Google Click ID (GCLID)')
                        ->disabled()
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('gad_source')
                        ->label('GAD Source')
                        ->helperText('Google Ads Source')
                        ->disabled()
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('gbraid')
                        ->label('GBRAID')
                        ->helperText('Web-to-App Measurement')
                        ->disabled()
                        ->columnSpan(2),
                    Forms\Components\TextInput::make('fbclid')
                        ->label('Facebook Click ID')
                        ->disabled()
                        ->columnSpan(2),
                ])
                ->collapsed()
                ->columns(2),
        ];
    }

    public static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->searchable()
                ->sortable()
                ->description(fn (Customer $record): string => $record->type === 'company' ? 'Company' : 'Personal'),
            
            Tables\Columns\TextColumn::make('email')
                ->searchable()
                ->toggleable()
                ->copyable()
                ->icon('heroicon-m-envelope')
                ->iconColor('primary')
                ->url(fn (Customer $record) => $record->email ? "mailto:{$record->email}" : null)
                ->openUrlInNewTab(),
            
            Tables\Columns\TextColumn::make('phone')
                ->searchable()
                ->toggleable()
                ->copyable()
                ->icon('heroicon-m-phone')
                ->iconColor('success')
                ->url(fn (Customer $record) => $record->phone ? "tel:{$record->phone}" : null),
            
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'lead' => 'warning',
                    'prospect' => 'info',
                    'customer' => 'success',
                    'inactive' => 'danger',
                    default => 'gray',
                }),
            
            Tables\Columns\TextColumn::make('assignedUser.name')
                ->label('Assigned To')
                ->badge()
                ->color('primary')
                ->sortable()
                ->toggleable(),
            
            Tables\Columns\SpatieTagsColumn::make('tags'), // Ensure plugin is installed or replace if needed
            
            Tables\Columns\TextColumn::make('followUps_count')
                ->counts('followUps')
                ->label('Follow-ups')
                ->badge()
                ->color('primary'),
            
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('type')
                ->options([
                    'personal' => 'Personal',
                    'company' => 'Company',
                ]),
            
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'lead' => 'Lead',
                    'prospect' => 'Prospect',
                    'customer' => 'Customer',
                    'inactive' => 'Inactive',
                ]),
            
            Tables\Filters\SelectFilter::make('assigned_to')
                ->label('Assigned To')
                ->relationship('assignedUser', 'name')
                ->searchable()
                ->preload()
                ->multiple(),
        ];
    }

    public static function getTableActions(): array
    {
        return [
            Tables\Actions\ActionGroup::make([
                // Quick Actions for Contact
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(function (Customer $record) {
                        $message = "Hi {$record->name}, this is a follow-up regarding our previous discussion.";
                        return get_whatsapp_url($record->phone, $record->country_code, $message);
                    })
                    ->openUrlInNewTab()
                    ->visible(fn (Customer $record) => !empty($record->phone)),
                
                Tables\Actions\Action::make('call')
                    ->label('Call')
                    ->icon('heroicon-o-phone')
                    ->color('info')
                    ->url(fn (Customer $record) => $record->phone ? "tel:{$record->phone}" : null)
                    ->visible(fn (Customer $record) => !empty($record->phone)),
                
                Tables\Actions\Action::make('email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->url(function (Customer $record) {
                        if (!$record->email) return null;
                        $subject = urlencode("Follow-up: {$record->name}");
                        $body = urlencode("Dear {$record->name},\n\nI hope this email finds you well.\n\nBest regards");
                        return "mailto:{$record->email}?subject={$subject}&body={$body}";
                    })
                    ->visible(fn (Customer $record) => !empty($record->email)),
                
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('reassign')
                    ->label('Reassign')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('new_assigned_to')
                            ->label('Reassign To')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->helperText('Select the new sales person'),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Reassignment Notes')
                            ->placeholder('Reason for reassignment...')
                            ->rows(3),
                    ])
                    ->action(function (Customer $record, array $data) {
                        $oldAssignee = $record->assigned_to;
                        
                        CustomerAssignment::create([
                            'customer_id' => $record->id,
                            'from_user_id' => $oldAssignee,
                            'to_user_id' => $data['new_assigned_to'],
                            'assigned_by' => auth()->id(),
                            'notes' => $data['notes'] ?? null,
                        ]);
                        
                        $record->update([
                            'assigned_to' => $data['new_assigned_to'],
                            'assigned_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Customer Reassigned')
                            ->success()
                            ->body("Customer has been reassigned successfully.")
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Reassign Customer')
                    ->modalDescription('This will change the sales person responsible for this customer.')
                    ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'sales_manager'])),
                
                Tables\Actions\DeleteAction::make(),
            ]),
        ];
    }

    public static function getTableBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\BulkAction::make('bulk_reassign')
                    ->label('Bulk Reassign')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('new_assigned_to')
                            ->label('Reassign To')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Reassignment Notes')
                            ->rows(3),
                    ])
                    ->action(function ($records, array $data) {
                        foreach ($records as $record) {
                            CustomerAssignment::create([
                                'customer_id' => $record->id,
                                'from_user_id' => $record->assigned_to,
                                'to_user_id' => $data['new_assigned_to'],
                                'assigned_by' => auth()->id(),
                                'notes' => 'Bulk reassignment: ' . ($data['notes'] ?? ''),
                            ]);
                            
                            $record->update([
                                'assigned_to' => $data['new_assigned_to'],
                                'assigned_at' => now(),
                            ]);
                        }
                        
                        Notification::make()
                            ->title('Customers Reassigned')
                            ->success()
                            ->body(count($records) . ' customers have been reassigned.')
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion()
                    ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'sales_manager'])),
                
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }

    public static function getTableHeaderActions(): array
    {
        return [
            Tables\Actions\Action::make('download_template')
                ->label('Download Template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    return Excel::download(
                        new CustomersTemplateExport(), 
                        'customers-import-template.xlsx'
                    );
                })
                ->tooltip('Download Excel template for importing customers'),
            
            Tables\Actions\Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function ($livewire) {
                    $filters = [];
                    $user = auth()->user();
                    
                    // Sales rep can only export their own customers
                    if (!$user->hasAnyRole(['super_admin', 'sales_manager'])) {
                        $filters['assigned_to'] = $user->id;
                    }
                    
                    // Get current filters
                    if ($livewire->tableFilters) {
                        if (isset($livewire->tableFilters['type']['value'])) {
                            $filters['type'] = $livewire->tableFilters['type']['value'];
                        }
                        if (isset($livewire->tableFilters['status']['value'])) {
                            $filters['status'] = $livewire->tableFilters['status']['value'];
                        }
                    }
                    
                    return Excel::download(
                        new CustomersExport($filters), 
                        'customers-' . now()->format('Y-m-d') . '.xlsx'
                    );
                }),
            
            Tables\Actions\Action::make('import')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('Excel File')
                        ->acceptedFileTypes([
                            'application/vnd.ms-excel', 
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($data['file']);
                        Excel::import(new CustomersImport, $filePath);
                        Notification::make()
                            ->title('Import successful')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                // ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'sales_manager'])),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
            'kanban' => Pages\CustomerKanban::route('/kanban'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        if (Auth::check()) {
            return (string) Customer::where('assigned_to', Auth::id())
                ->where('status', 'lead')
                ->count();
        }
        
        return null;
    }
}