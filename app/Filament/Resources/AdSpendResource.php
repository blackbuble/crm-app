<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdSpendResource\Pages;
use App\Models\AdSpend;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdSpendResource extends Resource
{
    protected static ?string $model = AdSpend::class;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Campaign Details')
                    ->schema([
                        Forms\Components\Select::make('platform')
                            ->options([
                                'Meta Ads' => 'Meta Ads (FB/IG)',
                                'Google Ads' => 'Google Ads',
                                'TikTok Ads' => 'TikTok Ads',
                                'LinkedIn Ads' => 'LinkedIn Ads',
                                'Twitter Ads' => 'Twitter/X Ads',
                                'Other' => 'Other',
                            ])
                            ->required()
                            ->searchable(),
                        Forms\Components\TextInput::make('campaign_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('amount')
                            ->label('Spend Amount')
                            ->numeric()
                            ->prefix(get_currency_symbol())
                            ->required(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Performance Metrics')
                    ->schema([
                        Forms\Components\TextInput::make('impressions')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('clicks')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('leads')
                            ->label('Leads Generated')
                            ->numeric()
                            ->default(0)
                            ->helperText('Manual count of leads/conversions from this campaign'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('platform')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('campaign_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn ($state) => format_currency($state))
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->formatStateUsing(fn ($state) => format_currency($state))),
                Tables\Columns\TextColumn::make('impressions')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('clicks')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('leads')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->options([
                        'Meta Ads' => 'Meta Ads',
                        'Google Ads' => 'Google Ads',
                        'TikTok Ads' => 'TikTok Ads',
                        'LinkedIn Ads' => 'LinkedIn Ads',
                        'Twitter Ads' => 'Twitter Ads',
                    ]),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from'),
                        Forms\Components\DatePicker::make('date_to'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['date_from'], fn ($q) => $q->whereDate('date', '>=', $data['date_from']))
                            ->when($data['date_to'], fn ($q) => $q->whereDate('date', '<=', $data['date_to']));
                    }),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdSpends::route('/'),
            'create' => Pages\CreateAdSpend::route('/create'),
            'edit' => Pages\EditAdSpend::route('/{record}/edit'),
        ];
    }
}
