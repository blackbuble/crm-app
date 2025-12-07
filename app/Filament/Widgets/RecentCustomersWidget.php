<?php
// app/Filament/Widgets/RecentCustomersWidget.php
namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentCustomersWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Customers')
            ->query(
                fn () => $this->getCustomerQuery()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Customer $record): string => $record->type === 'company' ? 'Company' : 'Personal'),
                
                Tables\Columns\TextColumn::make('email')
                    ->icon('heroicon-m-envelope')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->icon('heroicon-m-phone')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'lead',
                        'info' => 'prospect',
                        'success' => 'customer',
                        'danger' => 'inactive',
                    ]),
                
                Tables\Columns\TextColumn::make('assigned_user.name')
                    ->label('Assigned To')
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                
                Tables\Columns\SpatieTagsColumn::make('tags')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('followUps_count')
                    ->counts('followUps')
                    ->label('Follow-ups')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->since()
                    ->description(fn ($record) => $record->created_at->format('h:i A')),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Customer $record): string => route('filament.admin.resources.customers.edit', $record))
                    ->icon('heroicon-m-eye'),
            ]);
    }

    protected function getCustomerQuery(): Builder
    {
        $user = auth()->user();
        $query = Customer::query()->with(['assignedUser', 'followUps', 'tags']);
        
        // If not super admin, show only assigned customers
        if (!$user->hasRole('super_admin')) {
            $query->where('assigned_to', $user->id);
        }
        
        return $query;
    }
}