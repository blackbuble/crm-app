<?php
// app/Filament/Resources/CustomerResource/RelationManagers/FollowUpsRelationManager.php
namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class FollowUpsRelationManager extends RelationManager
{
    protected static string $relationship = 'followUps';
    protected static ?string $title = 'Follow-ups';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'whatsapp' => 'WhatsApp',
                        'phone' => 'Phone Call',
                        'email' => 'Email',
                    ])
                    ->required()
                    ->native(false),
                
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
                
                Forms\Components\SpatieTagsInput::make('tags')
                    ->label('Tags'),
                
                Forms\Components\Textarea::make('notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->defaultSort('follow_up_date', 'desc')
            ->columns([
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'whatsapp',
                        'info' => 'phone',
                        'warning' => 'email',
                    ]),
                
                Tables\Columns\TextColumn::make('follow_up_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('follow_up_time')
                    ->time(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                
                Tables\Columns\SpatieTagsColumn::make('tags'),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created by'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'whatsapp' => 'WhatsApp',
                        'phone' => 'Phone Call',
                        'email' => 'Email',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // WhatsApp Action
                    Tables\Actions\Action::make('whatsapp')
                        ->label('WhatsApp')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('success')
                        ->url(function ($record) {
                            $customer = $record->customer;
                            $phone = preg_replace('/[^0-9]/', '', $customer->phone);
                            
                            // Remove leading 0 and add country code if needed
                            if (substr($phone, 0, 1) === '0') {
                                $phone = '62' . substr($phone, 1);
                            }
                            
                            $message = urlencode("Hi {$customer->name}, this is a follow-up regarding our previous discussion.");
                            
                            return "https://wa.me/{$phone}?text={$message}";
                        })
                        ->openUrlInNewTab()
                        ->visible(fn ($record) => !empty($record->customer->phone)),
                    
                    // Phone Call Action
                    Tables\Actions\Action::make('call')
                        ->label('Call')
                        ->icon('heroicon-o-phone')
                        ->color('info')
                        ->url(fn ($record) => "tel:{$record->customer->phone}")
                        ->visible(fn ($record) => !empty($record->customer->phone)),
                    
                    // Email Action
                    Tables\Actions\Action::make('email')
                        ->label('Send Email')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->url(function ($record) {
                            $customer = $record->customer;
                            $subject = urlencode("Follow-up: {$customer->name}");
                            $body = urlencode("Dear {$customer->name},\n\nI hope this email finds you well. I wanted to follow up on our previous discussion.\n\nBest regards");
                            
                            return "mailto:{$customer->email}?subject={$subject}&body={$body}";
                        })
                        ->visible(fn ($record) => !empty($record->customer->email)),
                    
                    Tables\Actions\EditAction::make(),
                    
                    Tables\Actions\Action::make('complete')
                        ->label('Mark Complete')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('completion_notes')
                                ->label('Notes')
                                ->placeholder('Add notes about this follow-up...')
                                ->rows(3),
                        ])
                        ->action(function ($record, array $data) {
                            $notes = $record->notes;
                            if (!empty($data['completion_notes'])) {
                                $notes .= "\n\n[Completed: " . now()->format('Y-m-d H:i') . "]\n" . $data['completion_notes'];
                            }
                            
                            $record->update([
                                'status' => 'completed',
                                'completed_at' => now(),
                                'notes' => $notes,
                            ]);
                            
                            Notification::make()
                                ->title('Follow-up Completed')
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => $record->status === 'pending'),
                    
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_complete')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'completed',
                                    'completed_at' => now(),
                                ]);
                            }
                            
                            Notification::make()
                                ->title('Follow-ups Completed')
                                ->success()
                                ->body(count($records) . ' follow-ups marked as completed.')
                                ->send();
                        }),
                    
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}