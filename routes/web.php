<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuotationPdfController;

Route::get('/healthcheck', function () {
    return response('OK', 200)->header('Content-Type', 'text/plain');
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/quotations/{quotation}/pdf', [QuotationPdfController::class, 'generate'])
    ->name('quotation.pdf')
    ->middleware('auth');

// Public Lead Form Routes
Route::get('/contact-us', [App\Http\Controllers\PublicLeadController::class, 'showForm'])->name('lead.form');
Route::post('/contact-us', [App\Http\Controllers\PublicLeadController::class, 'store'])->name('lead.store');
