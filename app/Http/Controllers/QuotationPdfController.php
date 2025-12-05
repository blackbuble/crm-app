<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class QuotationPdfController extends Controller
{
    public function generate(Quotation $quotation)
    {
        $quotation->load(['customer', 'items']);
        
        $pdf = Pdf::loadView('quotations.pdf', compact('quotation'));
        
        return $pdf->download("quotation-{$quotation->quotation_number}.pdf");
    }
}