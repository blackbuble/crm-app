<?php

namespace App\Exports;

use App\Models\Quotation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuotationsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Quotation::with(['customer', 'items', 'user']);

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['customer_id'])) {
            $query->where('customer_id', $this->filters['customer_id']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Quotation Number',
            'Customer',
            'Date',
            'Valid Until',
            'Subtotal',
            'Tax %',
            'Tax Amount',
            'Discount',
            'Total',
            'Status',
            'Items Count',
            'Created By',
            'Created At',
        ];
    }

    public function map($quotation): array
    {
        return [
            $quotation->quotation_number,
            $quotation->customer->name,
            $quotation->quotation_date->format('Y-m-d'),
            $quotation->valid_until->format('Y-m-d'),
            'Rp ' . number_format($quotation->subtotal, 0, ',', '.'),
            $quotation->tax_percentage . '%',
            'Rp ' . number_format($quotation->tax_amount, 0, ',', '.'),
            'Rp ' . number_format($quotation->discount, 0, ',', '.'),
            'Rp ' . number_format($quotation->total, 0, ',', '.'),
            ucfirst($quotation->status),
            $quotation->items->count(),
            $quotation->user->name,
            $quotation->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
