<?php
// app/Filament/Resources/QuotationResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\QuotationResource\Pages;
use App\Models\Quotation;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Exports\QuotationsExport;

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'CRM';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Quotation Details')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->preload(),
                        Forms\Components\TextInput::make('quotation_number')
                            ->label('Quotation Number')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(fn () => Quotation::generateQuotationNumber()),
                        Forms\Components\DatePicker::make('quotation_date')
                            ->required()
                            ->default(now())
                            ->native(false),
                        Forms\Components\DatePicker::make('valid_until')
                            ->required()
                            ->default(now()->addDays(30))
                            ->native(false),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Sent',
                                'accepted' => 'Accepted',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('draft')
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Textarea::make('description')
                                    ->required()
                                    ->rows(2)
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $set('total', $state * $get('unit_price'));
                                    }),
                                Forms\Components\TextInput::make('unit_price')
                                    ->numeric()
                                    ->required()
                                ->prefix(get_currency_symbol())
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $set('total', $state * $get('quantity'));
                                    }),
                                Forms\Components\TextInput::make('total')
                                    ->numeric()
                                ->prefix(get_currency_symbol())
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->addActionLabel('Add Item')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('tax_percentage')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('discount')
                            ->numeric()
                            ->default(0)
                            ->prefix(get_currency_symbol())
                            ->live(onBlur: true),
                        Forms\Components\Placeholder::make('subtotal')
                            ->content(function ($get) {
                                $items = $get('items') ?? [];
                                return format_currency(collect($items)->sum('total'), null, 2);
                            }),
                        Forms\Components\Placeholder::make('tax_amount')
                            ->content(function ($get) {
                                $items = $get('items') ?? [];
                                $subtotal = collect($items)->sum('total');
                                $taxAmount = ($subtotal * ($get('tax_percentage') ?? 0)) / 100;
                                return format_currency($taxAmount, null, 2);
                            }),
                        Forms\Components\Placeholder::make('grand_total')
                            ->content(function ($get) {
                                $items = $get('items') ?? [];
                                $subtotal = collect($items)->sum('total');
                                $taxAmount = ($subtotal * ($get('tax_percentage') ?? 0)) / 100;
                                $total = $subtotal + $taxAmount - ($get('discount') ?? 0);
                                return format_currency($total, null, 2);
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quotation_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quotation_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->formatStateUsing(fn ($state) => format_currency($state))
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'info' => 'sent',
                        'success' => 'accepted',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created by')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($livewire) {
                        $filters = [];
                        
                        if ($livewire->tableFilters) {
                            if (isset($livewire->tableFilters['status']['value'])) {
                                $filters['status'] = $livewire->tableFilters['status']['value'];
                            }
                            if (isset($livewire->tableFilters['customer']['value'])) {
                                $filters['customer_id'] = $livewire->tableFilters['customer']['value'];
                            }
                        }
                        
                        return Excel::download(
                            new QuotationsExport($filters), 
                            'quotations-' . now()->format('Y-m-d') . '.xlsx'
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (Quotation $record) => route('quotation.pdf', $record))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'edit' => Pages\EditQuotation::route('/{record}/edit'),
        ];
    }
}