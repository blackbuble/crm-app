<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuotationPdfController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/quotations/{quotation}/pdf', [QuotationPdfController::class, 'generate'])
    ->name('quotation.pdf')
    ->middleware('auth');