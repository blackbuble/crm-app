<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingMaterialResource\Pages;
use App\Models\MarketingMaterial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MarketingMaterialResource extends Resource
{
    protected static ?string $model = MarketingMaterial::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationGroup = 'Sales Toolkit';
    protected static ?string $modelLabel = 'Sales Asset';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Asset Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('type')
                            ->options(MarketingMaterial::getTypes())
                            ->required()
                            ->live(),
                            
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                            
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload File')
                            ->directory('marketing-materials')
                            ->visibility('public')
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['brochure', 'price_list', 'presentation', 'contract', 'other']))
                            ->downloadable()
                            ->openable(),
                            
                        Forms\Components\FileUpload::make('thumbnail_path')
                            ->label('Thumbnail (Optional)')
                            ->image()
                            ->directory('marketing-thumbnails')
                            ->visibility('public'),
                            
                        Forms\Components\Textarea::make('content')
                            ->label('Script / Text Content')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'script')
                            ->rows(10)
                            ->columnSpanFull(),
                            
                        Forms\Components\TextInput::make('content')
                             ->label('Calculator URL')
                             ->visible(fn (Forms\Get $get) => $get('type') === 'calculator')
                             ->url()
                             ->columnSpanFull(),
                    ]),
                    
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_path')
                    ->label('Thumb')
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->description(fn (MarketingMaterial $record) => $record->type),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(MarketingMaterial::getTypes()),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                     ->label('Open')
                     ->icon('heroicon-o-eye')
                     ->url(fn (MarketingMaterial $record) => 
                        match($record->type) {
                            'calculator' => $record->content,
                            'script' => null, // Script is text, view it in modal
                            default => $record->file_path ? \Illuminate\Support\Facades\Storage::url($record->file_path) : null
                        }
                     )
                     ->openUrlInNewTab()
                     ->visible(fn (MarketingMaterial $record) => $record->type !== 'script'),

                Tables\Actions\ViewAction::make(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketingMaterials::route('/'),
            'create' => Pages\CreateMarketingMaterial::route('/create'),
            'edit' => Pages\EditMarketingMaterial::route('/{record}/edit'),
        ];
    }
}
