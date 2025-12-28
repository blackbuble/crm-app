<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

class UserGuide extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Support';
    protected static ?string $title = 'User Guide';
    protected static ?int $navigationSort = 100;
    
    protected static string $view = 'filament.pages.user-guide';

    public ?string $activeFile = null;
    public ?string $guideContent = null;

    public function openGuide(string $file): void
    {
        $this->activeFile = $file;
        $path = base_path($file);

        if (File::exists($path)) {
            $content = File::get($path);
            $this->guideContent = Str::markdown($content);
        } else {
            $this->guideContent = "Guide file not found.";
        }
    }

    public function closeGuide(): void
    {
        $this->activeFile = null;
        $this->guideContent = null;
    }

    public function getRoleGuides(): array
    {
        $user = Auth::user();
        $guides = [];

        // 1. Core Workflow (Based on INTERNATIONAL_CRM_GUIDE.md structure)
        $guides[] = [
            'title' => 'Core Workflow',
            'icon' => 'heroicon-o-globe-asia-australia',
            'color' => 'primary',
            'steps' => [
                'Manage International Customers with dynamic country codes.',
                'Organize your hierarchy: Country Manager > Sales Manager > Sales Rep.',
                'Utilize Area/Region fields for localized sales tracking.',
                'Import bulk data using the updated Excel Template with country support.',
            ]
        ];

        // 2. Sales & Pipeline (Based on CALENDAR_KANBAN_QUICK_GUIDE.md)
        $guides[] = [
            'title' => 'Sales & Pipeline',
            'icon' => 'heroicon-o-view-columns',
            'color' => 'success',
            'steps' => [
                'Use the Trello-style Kanban Board to manage lead stages.',
                'Drag & Drop customers to update status in real-time.',
                'Track Follow-ups directly on the Kanban cards.',
                'Sync your schedules with upcoming Google Calendar integration.',
            ]
        ];

        // 3. Exhibition & Kiosk (Based on internal kiosk logic)
        $guides[] = [
            'title' => 'Exhibition Operations',
            'icon' => 'heroicon-o-bolt',
            'color' => 'warning',
            'steps' => [
                'Enter Quick Leads during exhibitions using the Kiosk mode.',
                'Get instant "Price Estimation" based on selected packages.',
                'Auto-calculate Lead Score based on visitor profile.',
                'Send instant WhatsApp greetings to lock in prospects.',
            ]
        ];

        if ($user->hasAnyRole(['super_admin', 'sales_manager', 'country_manager'])) {
            $guides[] = [
                'title' => 'Manager Analytics',
                'icon' => 'heroicon-o-presentation-chart-line',
                'color' => 'info',
                'steps' => [
                    'Review KPI Dashboard for team performance tracking.',
                    'Manage Exhibition event schedules and logistics.',
                    'Oversee WhatsApp message templates for team consistency.',
                    'Analyze lead quality scores and conversion funnels.',
                ]
            ];
        }

        if ($user->hasRole('super_admin')) {
            $guides[] = [
                'title' => 'System & Security',
                'icon' => 'heroicon-o-shield-check',
                'color' => 'danger',
                'steps' => [
                    'Configure granular permissions via Filament Shield.',
                    'Manage storage settings (Cloudflare R2 / S3).',
                    'Set up global Currency and Branding (Logo/Colors).',
                    'Monitor System Logs for audit and troubleshooting.',
                ]
            ];
        }

        return $guides;
    }

    public function getSystemDocs(): array
    {
        return [
            ['title' => 'User Role Guide', 'file' => 'ROLE_BASED_USER_GUIDE.md', 'icon' => 'heroicon-o-user-circle'],
            ['title' => 'International Guide', 'file' => 'INTERNATIONAL_CRM_GUIDE.md', 'icon' => 'heroicon-o-document-text'],
            ['title' => 'Kanban & Calendar', 'file' => 'CALENDAR_KANBAN_QUICK_GUIDE.md', 'icon' => 'heroicon-o-calendar-days'],
            ['title' => 'Customer Import', 'file' => 'CUSTOMER_IMPORT_TEMPLATE_GUIDE.md', 'icon' => 'heroicon-o-arrow-up-tray'],
            ['title' => 'Branding Guide', 'file' => 'LOGO_BRANDING_GUIDE.md', 'icon' => 'heroicon-o-paint-brush'],
        ];
    }
}
