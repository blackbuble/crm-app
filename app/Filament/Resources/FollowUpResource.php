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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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
                            ->required()
                            // Optional: Filter customers by assigned_to
                            ->getSearchResultsUsing(fn (string $search): array => 
                                Customer::query()
                                    ->where('name', 'like', "%{$search}%")
                                    ->where('assigned_to', Auth::id())
                                    ->limit(50)
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->getOptionLabelUsing(fn ($value): ?string => 
                                Customer::find($value)?->name
                            ),
                        
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
            ->modifyQueryUsing(fn (Builder $query) => 
                // Filter follow-ups where customer's assigned_to matches current user
                $query->whereHas('customer', function ($q) {
                    $q->where('assigned_to', Auth::id());
                })
            )
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
                
                Tables\Columns\TextColumn::make('customer.assignedUser.name')
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
                // Optional: Filter by customer's assigned_to if you want flexibility
                Tables\Filters\SelectFilter::make('assigned_to')
                    ->relationship('customer.assignedUser', 'name')
                    ->label('Filter by Assigned User')
                    ->preload(),
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

    // Optional: Display count badge in navigation for current user's follow-ups
    public static function getNavigationBadge(): ?string
    {
        if (Auth::check()) {
            return (string) FollowUp::whereHas('customer', function ($q) {
                $q->where('assigned_to', Auth::id());
            })
            ->where('status', 'pending')
            ->count();
        }
        
        return null;
    }
}