<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaTemplateResource\Pages;
use App\Models\WaTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class WaTemplateResource extends Resource
{
    protected static ?string $model = WaTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'WA Templates';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Template Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Owner (Sales)')
                            ->relationship('user', 'name')
                            ->default(Auth::id())
                            ->required()
                            ->searchable()
                            ->preload()
                            ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'sales_manager', 'country_manager'])),

                        Forms\Components\TextInput::make('category')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Greeting, Follow Up, Promo'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),

                        Forms\Components\Textarea::make('message')
                            ->required()
                            ->rows(10)
                            ->columnSpanFull()
                            ->helperText('Use placeholders: {name} for visitor name.')
                            ->placeholder("Hi {name}! Terima kasih sudah mampir..."),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'sales_manager', 'country_manager'])),

                Tables\Columns\TextColumn::make('message')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Sales Rep')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'sales_manager', 'country_manager'])),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status'),
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        if (! Auth::user()->hasAnyRole(['super_admin', 'sales_manager', 'country_manager'])) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWaTemplates::route('/'),
            'create' => Pages\CreateWaTemplate::route('/create'),
            'edit' => Pages\EditWaTemplate::route('/{record}/edit'),
        ];
    }
}
