<?php
// app/Filament/Resources/CustomerResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Imports\CustomersImport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ImportAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'CRM';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
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
                        Forms\Components\SpatieTagsInput::make('tags'),
                        Forms\Components\Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Customer $record): string => $record->type === 'company' ? 'Company' : 'Personal'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'lead',
                        'info' => 'prospect',
                        'success' => 'customer',
                        'danger' => 'inactive',
                    ]),
                Tables\Columns\SpatieTagsColumn::make('tags'),
                Tables\Columns\TextColumn::make('followUps_count')
                    ->counts('followUps')
                    ->label('Follow-ups')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('import')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label('Excel File')
                            ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        try {
                            Excel::import(new CustomersImport, $data['file']);
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
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FollowUpsRelationManager::class,
            RelationManagers\QuotationsRelationManager::class,
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
}