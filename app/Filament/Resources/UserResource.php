<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Users & Teams';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'sales_manager', 'country_manager']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255)
                            ->helperText('Leave blank to keep current password'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location & Area')
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
                            ->helperText('Phone country code'),
                        
                        Forms\Components\TextInput::make('area')
                            ->label('Area/Region')
                            ->maxLength(100)
                            ->helperText('e.g., Jakarta, Surabaya, Singapore Central, etc.')
                            ->placeholder('Enter sales area or region'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Role & Permissions')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Role')
                            ->relationship('roles', 'name')
                            ->options(Role::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->helperText('Select user role: Super Admin, Country Manager, Sales Manager, or Sales Rep'),
                        
                        Forms\Components\Select::make('manager_id')
                            ->label('Reports To (Manager)')
                            ->relationship('manager', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Select the manager this user reports to')
                            ->visible(function (Forms\Get $get) {
                                $roles = $get('roles');
                                if (is_array($roles)) {
                                    $roleNames = Role::whereIn('id', $roles)->pluck('name')->toArray();
                                    // Show manager field for Sales Manager and Sales Rep
                                    return in_array('sales_manager', $roleNames) || in_array('sales_rep', $roleNames);
                                }
                                return false;
                            }),
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
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),
                
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'country_manager' => 'warning',
                        'sales_manager' => 'success',
                        'sales_rep' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                
                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('area')
                    ->label('Area/Region')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('manager.name')
                    ->label('Reports To')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('assignedCustomers_count')
                    ->counts('assignedCustomers')
                    ->label('Customers')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('country')
                    ->options(get_countries())
                    ->searchable(),
                
                Tables\Filters\SelectFilter::make('manager_id')
                    ->label('Manager')
                    ->relationship('manager', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) User::count();
    }
}
