<?php
// app/Filament/Resources/CustomerResource/RelationManagers/FollowUpsRelationManager.php
namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]))
                    ->visible(fn ($record) => $record->status === 'pending'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}