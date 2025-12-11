<?php
// app/Filament/Widgets/TeamPerformanceWidget.php
namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Customer;
use App\Models\Quotation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class TeamPerformanceWidget extends BaseWidget
{
    use HasWidgetShield;
    
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Team Performance';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereHas('roles', function($q) {
                        $q->whereIn('name', ['sales_rep', 'sales_manager']);
                    })
                    ->with(['assignedCustomers', 'quotations'])
                    ->withCount('assignedCustomers') // Add this to enable sorting
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Sales Person')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record) => $record->email),
                
                Tables\Columns\TextColumn::make('assignedCustomers_count')
                    ->counts('assignedCustomers')
                    ->label('Total Customers')
                    ->badge()
                    ->color('primary')
                    ->sortable(false),
                
                Tables\Columns\TextColumn::make('new_customers_this_month')
                    ->label('New (Month)')
                    ->getStateUsing(function (User $record) {
                        return Customer::where('assigned_to', $record->id)
                            ->where('created_at', '>=', Carbon::now()->startOfMonth())
                            ->count();
                    })
                    ->badge()
                    ->color('success')
                    ->sortable(false),
                
                Tables\Columns\TextColumn::make('quotations_this_month')
                    ->label('Quotations')
                    ->getStateUsing(function (User $record) {
                        return Quotation::where('user_id', $record->id)
                            ->where('quotation_date', '>=', Carbon::now()->startOfMonth())
                            ->count();
                    })
                    ->badge()
                    ->color('info')
                    ->sortable(false),
                
                Tables\Columns\TextColumn::make('revenue_this_month')
                    ->label('Revenue (Month)')
                    ->getStateUsing(function (User $record) {
                        return Quotation::where('user_id', $record->id)
                            ->where('status', 'accepted')
                            ->where('quotation_date', '>=', Carbon::now()->startOfMonth())
                            ->sum('total');
                    })
                    ->money('IDR')
                    ->sortable(false),
                
                Tables\Columns\TextColumn::make('win_rate')
                    ->label('Win Rate')
                    ->getStateUsing(function (User $record) {
                        $total = Quotation::where('user_id', $record->id)
                            ->where('quotation_date', '>=', Carbon::now()->startOfMonth())
                            ->count();
                        $accepted = Quotation::where('user_id', $record->id)
                            ->where('status', 'accepted')
                            ->where('quotation_date', '>=', Carbon::now()->startOfMonth())
                            ->count();
                        return $total > 0 ? round(($accepted / $total) * 100, 1) . '%' : '0%';
                    })
                    ->badge()
                    ->color(function (string $state) {
                        $rate = (float) str_replace('%', '', $state);
                        return $rate >= 30 ? 'success' : ($rate >= 20 ? 'warning' : 'danger');
                    })
                    ->sortable(false),
                
                Tables\Columns\IconColumn::make('performance')
                    ->label('Status')
                    ->getStateUsing(function (User $record) {
                        $revenue = Quotation::where('user_id', $record->id)
                            ->where('status', 'accepted')
                            ->where('quotation_date', '>=', Carbon::now()->startOfMonth())
                            ->sum('total');
                        // Good performance if revenue > 50M IDR
                        return $revenue >= 50000000;
                    })
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning'),
            ])
            ->defaultSort('name', 'asc')
            ->paginated([10, 25, 50]);
    }
}