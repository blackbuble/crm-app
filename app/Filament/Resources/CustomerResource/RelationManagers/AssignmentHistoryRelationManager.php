<?php
// app/Filament/Resources/CustomerResource/RelationManagers/AssignmentHistoryRelationManager.php
namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AssignmentHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';
    protected static ?string $title = 'Assignment History';
    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('fromUser.name')
                    ->label('From')
                    ->badge()
                    ->color('gray')
                    ->default('Initial Assignment'),
                
                Tables\Columns\IconColumn::make('arrow')
                    ->label('')
                    ->default('heroicon-m-arrow-right')
                    ->icon('heroicon-m-arrow-right')
                    ->size('lg'),
                
                Tables\Columns\TextColumn::make('toUser.name')
                    ->label('To')
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('assignedBy.name')
                    ->label('Assigned By')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->wrap()
                    ->limit(50)
                    ->tooltip(function ($record): string {
                        return $record->notes ?? 'No notes';
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}