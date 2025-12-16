<?php

namespace App\Filament\Pages;

use App\Settings\StorageSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class ManageStorage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-server-stack';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $title = 'Storage Configuration';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    protected static string $view = 'filament.pages.manage-storage';

    public ?array $data = [];

    public function mount(StorageSettings $settings): void
    {
        $this->form->fill([
            'filesystem_driver' => $settings->filesystem_driver,
            'aws_access_key_id' => $settings->aws_access_key_id,
            'aws_secret_access_key' => $settings->aws_secret_access_key,
            'aws_default_region' => $settings->aws_default_region,
            'aws_bucket' => $settings->aws_bucket,
            'aws_url' => $settings->aws_url,
            'aws_endpoint' => $settings->aws_endpoint,
            'aws_use_path_style_endpoint' => $settings->aws_use_path_style_endpoint,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filesystem Driver')
                    ->description('Choose where files should be stored.')
                    ->schema([
                        Forms\Components\Select::make('filesystem_driver')
                            ->label('Active Disk')
                            ->options([
                                'local' => 'Local Storage (Public)',
                                's3' => 'S3 / Compatible Bucket (AWS, R2, MinIO, etc)',
                            ])
                            ->required()
                            ->default('local')
                            ->live(),
                    ]),

                Forms\Components\Section::make('S3 Bucket Configuration')
                    ->description('Enter your storage provider details. Works with AWS S3, Cloudflare R2, DigitalOcean Spaces, Wasabi, etc.')
                    ->visible(fn (Forms\Get $get) => $get('filesystem_driver') === 's3')
                    ->schema([
                        Forms\Components\TextInput::make('aws_access_key_id')
                            ->label('Access Key ID')
                            ->password()
                            ->revealable(),

                        Forms\Components\TextInput::make('aws_secret_access_key')
                            ->label('Secret Access Key')
                            ->password()
                            ->revealable(),

                        Forms\Components\TextInput::make('aws_default_region')
                            ->label('Region')
                            ->default('us-east-1')
                            ->placeholder('us-east-1, ap-southeast-1, etc.')
                            ->required(),

                        Forms\Components\TextInput::make('aws_bucket')
                            ->label('Bucket Name'),

                        Forms\Components\TextInput::make('aws_endpoint')
                            ->label('Endpoint URL (Optional)')
                            ->placeholder('https://<accountid>.r2.cloudflarestorage.com')
                            ->helperText('Required for non-AWS providers like Cloudflare R2, MinIO, DO Spaces.'),

                        Forms\Components\TextInput::make('aws_url')
                            ->label('Public URL (Optional)')
                            ->placeholder('https://cdn.example.com')
                            ->helperText('Custom domain for public file access (CNAME).'),

                        Forms\Components\Toggle::make('aws_use_path_style_endpoint')
                            ->label('Use Path Style Endpoint')
                            ->helperText('Enable for MinIO or some S3 compatible providers.')
                            ->default(false),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(StorageSettings $settings): void
    {
        $data = $this->form->getState();
        
        $settings->filesystem_driver = $data['filesystem_driver'];
        $settings->aws_access_key_id = $data['aws_access_key_id'];
        $settings->aws_secret_access_key = $data['aws_secret_access_key'];
        $settings->aws_default_region = $data['aws_default_region'];
        $settings->aws_bucket = $data['aws_bucket'];
        $settings->aws_url = $data['aws_url'];
        $settings->aws_endpoint = $data['aws_endpoint'];
        $settings->aws_use_path_style_endpoint = $data['aws_use_path_style_endpoint'];
        
        $settings->save();
        
        Notification::make() 
            ->success()
            ->title('Settings Saved')
            ->body('Storage configuration updated successfully.')
            ->send();
            
        // Check for S3 driver requirement
        if ($data['filesystem_driver'] === 's3' && ! class_exists(\League\Flysystem\AwsS3V3\AwsS3V3Adapter::class)) {
             Notification::make()
                ->warning()
                ->title('Missing Dependency')
                ->body('Please run "composer require league/flysystem-aws-s3-v3" to enable S3.')
                ->persistent()
                ->send();
        }
    }
    
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->submit('save'),
        ];
    }
}
