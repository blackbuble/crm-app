<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KpiTargetResource\Pages;
use App\Models\KpiTarget;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class KpiTargetResource extends Resource
{
    protected static ?string $model = KpiTarget::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Sales Operations';
    protected static ?string $navigationLabel = 'KPI Targets';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Target Period')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Sales Person')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Select the sales person for this target'),
                        
                        Forms\Components\Select::make('period_type')
                            ->label('Period Type')
                            ->options([
                                'monthly' => 'Monthly',
                                'quarterly' => 'Quarterly',
                                'yearly' => 'Yearly',
                            ])
                            ->required()
                            ->live()
                            ->default('monthly'),
                        
                        Forms\Components\Select::make('year')
                            ->label('Year')
                            ->options(function () {
                                $years = [];
                                for ($i = date('Y') - 1; $i <= date('Y') + 2; $i++) {
                                    $years[$i] = $i;
                                }
                                return $years;
                            })
                            ->required()
                            ->default(date('Y')),
                        
                        Forms\Components\Select::make('period')
                            ->label('Period')
                            ->options(function (Forms\Get $get) {
                                return match($get('period_type')) {
                                    'monthly' => [
                                        1 => 'January', 2 => 'February', 3 => 'March',
                                        4 => 'April', 5 => 'May', 6 => 'June',
                                        7 => 'July', 8 => 'August', 9 => 'September',
                                        10 => 'October', 11 => 'November', 12 => 'December',
                                    ],
                                    'quarterly' => [
                                        1 => 'Q1 (Jan-Mar)',
                                        2 => 'Q2 (Apr-Jun)',
                                        3 => 'Q3 (Jul-Sep)',
                                        4 => 'Q4 (Oct-Dec)',
                                    ],
                                    'yearly' => [1 => 'Full Year'],
                                    default => [],
                                };
                            })
                            ->required()
                            ->default(fn (Forms\Get $get) => $get('period_type') === 'yearly' ? 1 : date('n')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Revenue & Customer Targets')
                    ->schema([
                        Forms\Components\TextInput::make('revenue_target')
                            ->label('Revenue Target (' . get_currency_symbol() . ')')
                            ->numeric()
                            ->prefix(get_currency_symbol())
                            ->required()
                            ->default(0),
                        
                        Forms\Components\TextInput::make('new_customers_target')
                            ->label('New Customers Target')
                            ->numeric()
                            ->required()
                            ->default(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Activity Targets')
                    ->schema([
                        Forms\Components\TextInput::make('quotations_target')
                            ->label('Quotations Target')
                            ->numeric()
                            ->required()
                            ->default(0),
                        
                        Forms\Components\TextInput::make('followups_target')
                            ->label('Follow-ups Target')
                            ->numeric()
                            ->required()
                            ->default(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Performance Targets (%)')
                    ->schema([
                        Forms\Components\TextInput::make('conversion_rate_target')
                            ->label('Conversion Rate Target (%)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(20)
                            ->helperText('Lead to Customer conversion target'),
                        
                        Forms\Components\TextInput::make('win_rate_target')
                            ->label('Win Rate Target (%)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(30)
                            ->helperText('Quotation acceptance rate target'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Additional Notes')
                            ->rows(3)
                            ->placeholder('Any special notes or considerations for this target period...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Sales Person')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('period_type')
                    ->label('Period Type')
                    ->colors([
                        'primary' => 'monthly',
                        'success' => 'quarterly',
                        'warning' => 'yearly',
                    ]),
                
                Tables\Columns\TextColumn::make('period_label')
                    ->label('Period')
                    ->searchable(['year', 'period']),
                
                Tables\Columns\TextColumn::make('revenue_target')
                    ->label('Revenue Target')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('actual_revenue')
                    ->label('Actual Revenue')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('achievement_percentage')
                    ->label('Achievement')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => $state >= 100 ? 'success' : ($state >= 80 ? 'warning' : 'danger'))
                    ->weight('bold'),
                
                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->achievement_percentage >= 100)
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Sales Person')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('period_type')
                    ->options([
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly' => 'Yearly',
                    ]),
                
                Tables\Filters\SelectFilter::make('year')
                    ->options(function () {
                        $years = [];
                        for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++) {
                            $years[$i] = $i;
                        }
                        return $years;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('refresh')
                    ->label('Refresh')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function ($record) {
                        $record->calculateActuals();
                        Notification::make()
                            ->title('KPI Updated')
                            ->success()
                            ->body('Actual values have been recalculated.')
                            ->send();
                    }),
                
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('refresh_all')
                        ->label('Refresh All')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->calculateActuals();
                            }
                            Notification::make()
                                ->title('KPIs Updated')
                                ->success()
                                ->body(count($records) . ' KPIs have been recalculated.')
                                ->send();
                        }),
                    
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('year', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKpiTargets::route('/'),
            'create' => Pages\CreateKpiTarget::route('/create'),
            'edit' => Pages\EditKpiTarget::route('/{record}/edit'),
        ];
    }
}