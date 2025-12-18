<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PricingConfigResource\Pages;
use App\Models\PricingConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class PricingConfigResource extends Resource
{
    protected static ?string $model = PricingConfig::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Marketing Operations';
    protected static ?string $navigationLabel = 'Pricing Configs';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuration Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Unique identifier for this pricing configuration'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText('Only active configs will be available in calculator'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Upload JSON Configuration')
                    ->schema([
                        Forms\Components\FileUpload::make('config_file')
                            ->label('Upload Pricing JSON')
                            ->acceptedFileTypes(['application/json'])
                            ->maxSize(1024)
                            ->helperText('Upload a JSON file with packages, addons, and discount rules')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    try {
                                        $path = storage_path('app/public/' . $state);
                                        $json = file_get_contents($path);
                                        $config = json_decode($json, true);
                                        
                                        if (json_last_error() === JSON_ERROR_NONE) {
                                            $set('config', json_encode($config, JSON_PRETTY_PRINT));
                                            
                                            Notification::make()
                                                ->title('JSON loaded successfully')
                                                ->success()
                                                ->send();
                                        } else {
                                            Notification::make()
                                                ->title('Invalid JSON file')
                                                ->danger()
                                                ->send();
                                        }
                                    } catch (\Exception $e) {
                                        Notification::make()
                                            ->title('Error reading file')
                                            ->body($e->getMessage())
                                            ->danger()
                                            ->send();
                                    }
                                }
                            })
                            ->dehydrated(false),

                        Forms\Components\Textarea::make('config')
                            ->label('JSON Configuration')
                            ->rows(15)
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Edit the JSON configuration directly or upload a file above')
                            ->placeholder(self::getExampleJson()),
                    ]),
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

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('packages_count')
                    ->label('Packages')
                    ->getStateUsing(fn ($record) => count($record->getPackages()))
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('addons_count')
                    ->label('Add-ons')
                    ->getStateUsing(fn ($record) => count($record->getAddons()))
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Uploaded By')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Pricing Configuration Preview')
                    ->modalContent(fn ($record) => view('filament.modals.pricing-config-preview', ['config' => $record]))
                    ->modalSubmitAction(false),

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
            'index' => Pages\ListPricingConfigs::route('/'),
            'create' => Pages\CreatePricingConfig::route('/create'),
            'edit' => Pages\EditPricingConfig::route('/{record}/edit'),
        ];
    }

    protected static function getExampleJson(): string
    {
        return json_encode([
            'packages' => [
                [
                    'id' => 'pkg_basic',
                    'name' => 'Basic Package',
                    'description' => 'Essential features for small events',
                    'price' => 5000000,
                    'features' => ['Feature 1', 'Feature 2']
                ],
                [
                    'id' => 'pkg_premium',
                    'name' => 'Premium Package',
                    'description' => 'Complete solution for large events',
                    'price' => 10000000,
                    'features' => ['All Basic features', 'Feature 3', 'Feature 4']
                ]
            ],
            'addons' => [
                [
                    'id' => 'addon_photo',
                    'name' => 'Photo Booth',
                    'description' => 'Professional photo booth service',
                    'price' => 2000000
                ],
                [
                    'id' => 'addon_video',
                    'name' => 'Video Recording',
                    'description' => '4K video recording',
                    'price' => 3000000
                ]
            ],
            'discount_rules' => [
                [
                    'type' => 'minimum_spend',
                    'minimum' => 15000000,
                    'discount_type' => 'percentage',
                    'discount_value' => 10,
                    'description' => '10% off for orders above 15M'
                ],
                [
                    'type' => 'package_count',
                    'minimum_packages' => 2,
                    'discount_type' => 'fixed',
                    'discount_value' => 1000000,
                    'description' => '1M off when buying 2+ packages'
                ]
            ]
        ], JSON_PRETTY_PRINT);
    }
}
