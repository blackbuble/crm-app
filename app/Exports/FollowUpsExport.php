<?php

namespace App\Exports;

use App\Models\FollowUp;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FollowUpsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = FollowUp::with(['customer', 'user', 'tags']);

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
            'Customer',
            'Type',
            'Follow-up Date',
            'Follow-up Time',
            'Status',
            'Tags',
            'Notes',
            'Assigned To',
            'Completed At',
            'Created At',
        ];
    }

    public function map($followUp): array
    {
        return [
            $followUp->id,
            $followUp->customer->name,
            ucfirst($followUp->type),
            $followUp->follow_up_date->format('Y-m-d'),
            $followUp->follow_up_time?->format('H:i'),
            ucfirst($followUp->status),
            $followUp->tags->pluck('name')->implode(', '),
            $followUp->notes,
            $followUp->user->name,
            $followUp->completed_at?->format('Y-m-d H:i:s'),
            $followUp->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
