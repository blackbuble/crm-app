<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class QuotationPdfController extends Controller
{
    public function generate(Quotation $quotation): Response
    {
        $quotation->load(['customer', 'items']);
        
        $pdf = Pdf::loadView('quotations.pdf', compact('quotation'));
        
        return $pdf->download("quotation-{$quotation->quotation_number}.pdf");
    }
}