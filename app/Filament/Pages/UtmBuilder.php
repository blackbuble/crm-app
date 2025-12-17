<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Notifications\Notification;

class UtmBuilder extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'Marketing Operations';
    protected static ?string $title = 'UTM Link Builder';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.utm-builder';

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_UtmBuilder');
    }

    public ?array $data = [];
    public ?string $generatedUrl = '';
    public array $bulkGeneratedUrls = [];

    public function mount(): void
    {
        $this->form->fill([
            'base_url' => url('/contact-us'),
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'bulk_urls' => '',
            'mode' => 'single',
        ]);
        $this->generateUrl();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Builder Mode')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Single Link')
                            ->icon('heroicon-m-link')
                            ->schema([
                                Forms\Components\Section::make('Destination')
                                    ->schema([
                                        Forms\Components\TextInput::make('base_url')
                                            ->label('Website URL')
                                            ->placeholder('https://yourwebsite.com/page')
                                            ->required()
                                            ->url()
                                            ->prefixIcon('heroicon-m-globe-alt')
                                            ->columnSpanFull()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn () => $this->generateUrl()),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Bulk Generate')
                            ->icon('heroicon-m-queue-list')
                            ->schema([
                                Forms\Components\Section::make('Destinations')
                                    ->description('Enter multiple URLs, one per line.')
                                    ->schema([
                                        Forms\Components\Textarea::make('bulk_urls')
                                            ->label('Website URLs')
                                            ->placeholder("https://site.com/page1\nhttps://site.com/page2")
                                            ->rows(5)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn () => $this->generateBulkUrls()),
                                    ]),
                            ]),
                    ]),

                Forms\Components\Section::make('Tracking Information')
                    ->description('These parameters will be applied to all your links.')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('utm_source')
                                            ->label('Source (Where?)')
                                            ->placeholder('facebook, google, newsletter')
                                            ->datalist(fn() => array_unique(array_merge(
                                                [
                                                    'facebook', 'google', 'instagram', 'tiktok', 
                                                    'linkedin', 'twitter', 'whatsapp', 'email', 'youtube'
                                                ],
                                                \App\Models\Customer::distinct()->pluck('utm_source')->filter()->toArray(),
                                                \App\Models\AdSpend::distinct()->pluck('platform')->filter()->toArray()
                                            )))
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function () {
                                                $this->generateUrl();
                                                $this->generateBulkUrls();
                                            }),
                                    ]),
                                
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('utm_medium')
                                            ->label('Medium (How?)')
                                            ->placeholder('cpc, banner, email')
                                            ->datalist(fn() => array_unique(array_merge(
                                                [
                                                    'cpc', 'cpm', 'email', 'social', 
                                                    'referral', 'organic', 'banner', 'story'
                                                ],
                                                \App\Models\Customer::distinct()->pluck('utm_medium')->filter()->toArray()
                                            )))
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function () {
                                                $this->generateUrl();
                                                $this->generateBulkUrls();
                                            })
                                    ]),

                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('utm_campaign')
                                            ->label('Campaign Name (Why?)')
                                            ->placeholder('summer_sale')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function () {
                                                $this->generateUrl();
                                                $this->generateBulkUrls();
                                            })
                                    ]),
                            ]),
                    ]),

                Forms\Components\Section::make('Optional Details')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('utm_term')
                                    ->label('Campaign Term')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function () {
                                        $this->generateUrl();
                                        $this->generateBulkUrls();
                                    }),

                                Forms\Components\TextInput::make('utm_content')
                                    ->label('Campaign Content')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function () {
                                        $this->generateUrl();
                                        $this->generateBulkUrls();
                                    }),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function generateUrl(): void
    {
        $data = $this->form->getState();
        $this->generatedUrl = $this->buildUtmLink($data['base_url'] ?? '', $data);
    }

    public function generateBulkUrls(): void
    {
        $data = $this->form->getState();
        $urls = explode("\n", $data['bulk_urls'] ?? '');
        $this->bulkGeneratedUrls = [];

        foreach ($urls as $url) {
            $url = trim($url);
            if (!empty($url)) {
                $this->bulkGeneratedUrls[] = $this->buildUtmLink($url, $data);
            }
        }
    }

    protected function buildUtmLink(string $url, array $data): string
    {
        if (empty($url)) return '';

        $url = explode('?', $url)[0];
        
        $params = array_filter([
            'utm_source' => $data['utm_source'] ?? null,
            'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_term' => $data['utm_term'] ?? null,
            'utm_content' => $data['utm_content'] ?? null,
        ], fn($value) => !is_null($value) && $value !== '');

        if (!empty($params)) {
            return $url . '?' . http_build_query($params);
        }

        return $url;
    }
}
