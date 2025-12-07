<?php
// app/Filament/Pages/Reports.php
namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Quotation;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'CRM';
    protected static ?int $navigationSort = 10;
    protected static string $view = 'filament.pages.reports';

    public ?array $data = [];
    public $startDate;
    public $endDate;
    public $reportType = 'overview';

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->form->fill([
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'report_type' => $this->reportType,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->default(now()->startOfMonth())
                    ->native(false)
                    ->required(),
                DatePicker::make('end_date')
                    ->label('End Date')
                    ->default(now()->endOfMonth())
                    ->native(false)
                    ->required(),
                Select::make('report_type')
                    ->label('Report Type')
                    ->options([
                        'overview' => 'Overview',
                        'customers' => 'Customers',
                        'followups' => 'Follow-ups',
                        'quotations' => 'Quotations',
                        'performance' => 'Performance',
                    ])
                    ->default('overview')
                    ->native(false)
                    ->required(),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function generateReport(): void
    {
        $data = $this->form->getState();
        $this->startDate = $data['start_date'];
        $this->endDate = $data['end_date'];
        $this->reportType = $data['report_type'];
    }

    public function getReportData(): array
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        return match($this->reportType) {
            'customers' => $this->getCustomerReport($start, $end),
            'followups' => $this->getFollowUpReport($start, $end),
            'quotations' => $this->getQuotationReport($start, $end),
            'performance' => $this->getPerformanceReport($start, $end),
            default => $this->getOverviewReport($start, $end),
        };
    }

    protected function getOverviewReport($start, $end): array
    {
        return [
            'total_customers' => Customer::whereBetween('created_at', [$start, $end])->count(),
            'customers_by_status' => [
                'leads' => Customer::where('status', 'lead')->whereBetween('created_at', [$start, $end])->count(),
                'prospects' => Customer::where('status', 'prospect')->whereBetween('created_at', [$start, $end])->count(),
                'customers' => Customer::where('status', 'customer')->whereBetween('created_at', [$start, $end])->count(),
                'inactive' => Customer::where('status', 'inactive')->whereBetween('created_at', [$start, $end])->count(),
            ],
            'total_followups' => FollowUp::whereBetween('created_at', [$start, $end])->count(),
            'completed_followups' => FollowUp::where('status', 'completed')->whereBetween('created_at', [$start, $end])->count(),
            'total_quotations' => Quotation::whereBetween('created_at', [$start, $end])->sum('total'),
            'accepted_quotations' => Quotation::where('status', 'accepted')->whereBetween('created_at', [$start, $end])->sum('total'),
            'quotation_count' => Quotation::whereBetween('created_at', [$start, $end])->count(),
        ];
    }

    protected function getCustomerReport($start, $end): array
    {
        return [
            'new_customers' => Customer::whereBetween('created_at', [$start, $end])->get(),
            'by_type' => [
                'company' => Customer::where('type', 'company')->whereBetween('created_at', [$start, $end])->count(),
                'personal' => Customer::where('type', 'personal')->whereBetween('created_at', [$start, $end])->count(),
            ],
            'top_customers' => Customer::withCount('quotations')
                ->whereBetween('created_at', [$start, $end])
                ->orderBy('quotations_count', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    protected function getFollowUpReport($start, $end): array
    {
        return [
            'total' => FollowUp::whereBetween('follow_up_date', [$start, $end])->count(),
            'by_type' => [
                'whatsapp' => FollowUp::where('type', 'whatsapp')->whereBetween('follow_up_date', [$start, $end])->count(),
                'phone' => FollowUp::where('type', 'phone')->whereBetween('follow_up_date', [$start, $end])->count(),
                'email' => FollowUp::where('type', 'email')->whereBetween('follow_up_date', [$start, $end])->count(),
            ],
            'by_status' => [
                'pending' => FollowUp::where('status', 'pending')->whereBetween('follow_up_date', [$start, $end])->count(),
                'completed' => FollowUp::where('status', 'completed')->whereBetween('follow_up_date', [$start, $end])->count(),
                'cancelled' => FollowUp::where('status', 'cancelled')->whereBetween('follow_up_date', [$start, $end])->count(),
            ],
            'completion_rate' => $this->calculateCompletionRate($start, $end),
        ];
    }

    protected function getQuotationReport($start, $end): array
    {
        $quotations = Quotation::whereBetween('quotation_date', [$start, $end])->get();
        
        return [
            'total_value' => $quotations->sum('total'),
            'average_value' => $quotations->avg('total'),
            'count' => $quotations->count(),
            'by_status' => [
                'draft' => Quotation::where('status', 'draft')->whereBetween('quotation_date', [$start, $end])->count(),
                'sent' => Quotation::where('status', 'sent')->whereBetween('quotation_date', [$start, $end])->count(),
                'accepted' => Quotation::where('status', 'accepted')->whereBetween('quotation_date', [$start, $end])->count(),
                'rejected' => Quotation::where('status', 'rejected')->whereBetween('quotation_date', [$start, $end])->count(),
            ],
            'conversion_rate' => $this->calculateConversionRate($start, $end),
            'top_quotations' => Quotation::whereBetween('quotation_date', [$start, $end])
                ->orderBy('total', 'desc')
                ->limit(10)
                ->with('customer')
                ->get(),
        ];
    }

    protected function getPerformanceReport($start, $end): array
    {
        return [
            'users_performance' => \App\Models\User::withCount([
                'quotations' => fn($q) => $q->whereBetween('created_at', [$start, $end])
            ])->get(),
            'daily_stats' => $this->getDailyStats($start, $end),
        ];
    }

    protected function calculateCompletionRate($start, $end): float
    {
        $total = FollowUp::whereBetween('follow_up_date', [$start, $end])->count();
        $completed = FollowUp::where('status', 'completed')->whereBetween('follow_up_date', [$start, $end])->count();
        
        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    protected function calculateConversionRate($start, $end): float
    {
        $total = Quotation::whereBetween('quotation_date', [$start, $end])->count();
        $accepted = Quotation::where('status', 'accepted')->whereBetween('quotation_date', [$start, $end])->count();
        
        return $total > 0 ? round(($accepted / $total) * 100, 2) : 0;
    }

    protected function getDailyStats($start, $end): array
    {
        $days = [];
        $current = Carbon::parse($start);
        
        while ($current <= Carbon::parse($end)) {
            $days[] = [
                'date' => $current->format('Y-m-d'),
                'customers' => Customer::whereDate('created_at', $current)->count(),
                'followups' => FollowUp::whereDate('follow_up_date', $current)->count(),
                'quotations' => Quotation::whereDate('quotation_date', $current)->count(),
            ];
            $current->addDay();
        }
        
        return $days;
    }
}