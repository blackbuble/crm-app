<?php
// app/Exports/CustomersExport.php
namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Customer::with(['followUps', 'quotations', 'tags']);

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Type',
            'Name',
            'Email',
            'Phone',
            'Address',
            'Company Name',
            'Tax ID',
            'First Name',
            'Last Name',
            'Status',
            'Tags',
            'Follow-ups Count',
            'Quotations Count',
            'Total Quotations Value',
            'Created At',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            ucfirst($customer->type),
            $customer->name,
            $customer->email,
            $customer->phone,
            $customer->address,
            $customer->company_name,
            $customer->tax_id,
            $customer->first_name,
            $customer->last_name,
            ucfirst($customer->status),
            $customer->tags->pluck('name')->implode(', '),
            $customer->followUps->count(),
            $customer->quotations->count(),
            'Rp ' . number_format($customer->quotations->sum('total'), 0, ',', '.'),
            $customer->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}