# Dynamic Currency Implementation

## Overview
Aplikasi CRM sekarang mendukung format mata uang yang dinamis. Semua format mata uang tidak lagi hardcoded "Rp" tetapi menggunakan helper function yang dapat dikonfigurasi.

## Konfigurasi

### Environment Variable
Tambahkan di file `.env`:
```bash
APP_CURRENCY=IDR
```

### Mata Uang yang Didukung
- **IDR** - Indonesian Rupiah (Rp)
- **USD** - US Dollar ($)
- **EUR** - Euro (€)
- **GBP** - British Pound (£)
- **JPY** - Japanese Yen (¥)
- **SGD** - Singapore Dollar (S$)
- **MYR** - Malaysian Ringgit (RM)

## Helper Functions

### 1. `format_currency($amount, $currency = null, $decimals = 0)`
Format angka menjadi format mata uang.

**Contoh:**
```php
format_currency(1000000)           // Output: Rp 1.000.000
format_currency(1000000, 'USD')    // Output: $1,000,000
format_currency(1000000, 'EUR', 2) // Output: €1,000,000.00
```

### 2. `get_currency_symbol($currency = null)`
Mendapatkan simbol mata uang.

**Contoh:**
```php
get_currency_symbol()        // Output: Rp (dari config)
get_currency_symbol('USD')   // Output: $
get_currency_symbol('EUR')   // Output: €
```

## File yang Diupdate

### Helper
- ✅ `app/Helpers/currency.php` - Helper functions baru

### Configuration
- ✅ `config/app.php` - Menambahkan `currency` config
- ✅ `composer.json` - Autoload helper file
- ✅ `.env.example` - Menambahkan `APP_CURRENCY`

### Observers
- ✅ `app/Observers/QuotationObserver.php`

### Widgets
- ✅ `app/Filament/Widgets/SalesRepStatsWidget.php`
- ✅ `app/Filament/Widgets/KpiWidget.php`
- ✅ `app/Filament/Widgets/CustomerStatsWidget.php`

### Resources
- ✅ `app/Filament/Resources/QuotationResource.php`
- ✅ `app/Filament/Resources/KpiTargetResource.php`

### Exports
- ✅ `app/Exports/QuotationsExport.php`
- ✅ `app/Exports/CustomersExport.php`

## Cara Menggunakan

### 1. Install Dependencies
```bash
composer dump-autoload
```

### 2. Update .env
```bash
APP_CURRENCY=IDR
```

### 3. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
```

## Format Mata Uang

### IDR (Indonesian Rupiah)
- Format: `Rp 1.000.000`
- Separator: titik untuk ribuan, koma untuk desimal
- Default decimals: 0

### USD/EUR/GBP/JPY/SGD/MYR
- Format: `$1,000,000.00`
- Separator: koma untuk ribuan, titik untuk desimal
- Default decimals: 0 (bisa diubah dengan parameter)

## Contoh Penggunaan dalam Kode

### Dalam Filament Resource
```php
// Form Input
Forms\Components\TextInput::make('price')
    ->prefix(get_currency_symbol())
    ->numeric();

// Table Column
Tables\Columns\TextColumn::make('total')
    ->formatStateUsing(fn ($state) => format_currency($state));

// Placeholder
Forms\Components\Placeholder::make('total')
    ->content(fn ($get) => format_currency($get('amount'), null, 2));
```

### Dalam Widget
```php
Stat::make('Revenue', format_currency($revenue))
    ->description('This month')
    ->color('success');
```

### Dalam Notification
```php
Notification::make()
    ->title('Deal Closed!')
    ->body("Worth " . format_currency($quotation->total))
    ->send();
```

### Dalam Export
```php
public function map($record): array
{
    return [
        $record->name,
        format_currency($record->total),
        // ...
    ];
}
```

## Keuntungan

1. **Fleksibilitas**: Mudah mengganti mata uang tanpa mengubah kode
2. **Konsistensi**: Format mata uang konsisten di seluruh aplikasi
3. **Internasionalisasi**: Siap untuk multi-currency
4. **Maintainability**: Satu tempat untuk mengatur format mata uang
5. **Scalability**: Mudah menambahkan mata uang baru

## Menambahkan Mata Uang Baru

Edit file `app/Helpers/currency.php`:

```php
$symbols = [
    'IDR' => 'Rp',
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'JPY' => '¥',
    'SGD' => 'S$',
    'MYR' => 'RM',
    'THB' => '฿',  // Tambahkan mata uang baru
];
```

## Testing

Untuk menguji dengan mata uang berbeda:

1. Update `.env`:
```bash
APP_CURRENCY=USD
```

2. Clear cache:
```bash
php artisan config:clear
```

3. Refresh halaman dan lihat semua nilai mata uang berubah menjadi format USD

## Notes

- Semua hardcoded "Rp" telah diganti dengan `format_currency()` atau `get_currency_symbol()`
- Format default adalah IDR (Indonesian Rupiah)
- Untuk menampilkan desimal, gunakan parameter ketiga: `format_currency($amount, null, 2)`
