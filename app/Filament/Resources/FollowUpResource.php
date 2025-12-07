<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FollowUpResource\Pages;
use App\Models\FollowUp;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class FollowUpResource extends Resource
{
    protected static ?string $model = FollowUp::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'CRM';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Follow-ups';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer & Type')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\Select::make('type')
                            ->options([
                                'whatsapp' => 'WhatsApp',
                                'phone' => 'Phone Call',
                                'email' => 'Email',
                            ])
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Schedule')
                    ->schema([
                        Forms\Components\DatePicker::make('follow_up_date')
                            ->required()
                            ->native(false)
                            ->default(now()),
                        
                        Forms\Components\TimePicker::make('follow_up_time')
                            ->native(false),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending')
                            ->native(false),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\SpatieTagsInput::make('tags'),
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
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->customer->phone),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'whatsapp',
                        'info' => 'phone',
                        'warning' => 'email',
                    ]),
                
                Tables\Columns\TextColumn::make('follow_up_date')
                    ->date('M d, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->follow_up_time?->format('h:i A')),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                
                Tables\Columns\SpatieTagsColumn::make('tags'),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Assigned To')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type'),
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\Filter::make('today')
                    ->query(fn ($query) => $query->whereDate('follow_up_date', today()))
                    ->label('Today'),
                Tables\Filters\Filter::make('overdue')
                    ->query(fn ($query) => $query->where('status', 'pending')->where('follow_up_date', '<', today()))
                    ->label('Overdue'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('contact')
                        ->label('Contact Customer')
                        ->icon('heroicon-o-phone-arrow-up-right')
                        ->color('primary')
                        ->modalHeading('Contact Customer')
                        ->modalContent(fn ($record) => view('filament.actions.contact-customer', ['record' => $record]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),
                    
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->defaultSort('follow_up_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFollowUps::route('/'),
            'create' => Pages\CreateFollowUp::route('/create'),
            'edit' => Pages\EditFollowUp::route('/{record}/edit'),
        ];
    }
}