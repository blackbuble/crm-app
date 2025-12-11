<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class MyCustomersWidget extends BaseWidget
{
    use HasWidgetShield;
    
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('My Customers by Status')
            ->query(
                Customer::query()
                    ->where('assigned_to', auth()->id())
                    ->with(['followUps', 'quotations', 'tags'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->description(fn (Customer $record): string => $record->email ?? ''),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'lead',
                        'info' => 'prospect',
                        'success' => 'customer',
                        'danger' => 'inactive',
                    ]),
                
                Tables\Columns\TextColumn::make('phone')
                    ->icon('heroicon-m-phone'),
                
                Tables\Columns\TextColumn::make('next_followup')
                    ->label('Next Follow-up')
                    ->getStateUsing(function (Customer $record) {
                        $nextFollowUp = $record->followUps()
                            ->where('status', 'pending')
                            ->where('follow_up_date', '>=', now())
                            ->orderBy('follow_up_date')
                            ->first();
                        
                        return $nextFollowUp 
                            ? $nextFollowUp->follow_up_date->format('M d, Y') 
                            : '-';
                    })
                    ->badge()
                    ->color(fn ($state) => $state === '-' ? 'gray' : 'info'),
                
                Tables\Columns\TextColumn::make('quotations_sum_total')
                    ->sum('quotations', 'total')
                    ->label('Total Value')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'lead' => 'Lead',
                        'prospect' => 'Prospect',
                        'customer' => 'Customer',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Customer $record): string => route('filament.admin.resources.customers.edit', $record))
                    ->icon('heroicon-m-eye'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
