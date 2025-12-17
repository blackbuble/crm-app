<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExhibitionResource\Pages;
use App\Models\Exhibition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExhibitionResource extends Resource
{
    protected static ?string $model = Exhibition::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Marketing Operations';
    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasAnyRole(['super_admin', 'sales_manager', 'country_manager'])) {
            return $query;
        }

        return $query->where('created_by', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Event Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('location')
                            ->maxLength(255),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('start_date')
                                    ->required(),
                                Forms\Components\DatePicker::make('end_date')
                                    ->required()
                                    ->afterOrEqual('start_date'),
                            ]),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Financials')
                    ->schema([
                        Forms\Components\TextInput::make('booth_cost')
                            ->label('Booth Rental Cost')
                            ->numeric()
                            ->prefix(get_currency_symbol())
                            ->default(0),
                        Forms\Components\TextInput::make('operational_cost')
                            ->label('Other Operational Costs')
                            ->helperText('Staff, electricity, logistics, etc.')
                            ->numeric()
                            ->prefix(get_currency_symbol())
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Total Cost')
                    ->money(get_currency_symbol())
                    ->state(fn (Exhibition $record) => $record->total_cost),
                Tables\Columns\TextColumn::make('customers_count')
                    ->counts('customers')
                    ->label('Leads'),
                Tables\Columns\TextColumn::make('quotations_count')
                    ->counts('quotations')
                    ->label('Quotations'),
                Tables\Columns\TextColumn::make('revenue')
                    ->label('Revenue')
                    ->money(get_currency_symbol())
                    ->state(fn (Exhibition $record) => $record->total_revenue)
                    ->color('success'),
                Tables\Columns\TextColumn::make('roi')
                    ->label('ROI')
                    ->suffix('%')
                    ->state(function (Exhibition $record) {
                        $roi = $record->roi;
                        return number_format($roi, 1);
                    })
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            // We can add CustomerRelationManager later
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExhibitions::route('/'),
            'create' => Pages\CreateExhibition::route('/create'),
            'edit' => Pages\EditExhibition::route('/{record}/edit'),
        ];
    }
}
