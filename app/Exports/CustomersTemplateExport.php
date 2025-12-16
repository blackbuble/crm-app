<?php
// app/Exports/CustomersTemplateExport.php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CustomersTemplateExport implements WithHeadings, WithStyles, WithColumnWidths, ShouldAutoSize
{
    /**
     * Define the headings for the template
     */
    public function headings(): array
    {
        return [
            'type',
            'name',
            'email',
            'phone',
            'country',
            'country_code',
            'address',
            'company_name',
            'tax_id',
            'first_name',
            'last_name',
            'notes',
            'status',
            'tags',
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'], // Indigo color
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Add instructions in row 2
        $sheet->setCellValue('A2', 'company or personal');
        $sheet->setCellValue('B2', 'Full name or leave empty if using first_name/last_name');
        $sheet->setCellValue('C2', 'email@example.com');
        $sheet->setCellValue('D2', 'Phone without country code');
        $sheet->setCellValue('E2', 'Indonesia, Singapore, etc.');
        $sheet->setCellValue('F2', '+62, +65, +1, etc.');
        $sheet->setCellValue('G2', 'Full address');
        $sheet->setCellValue('H2', 'Required if type=company');
        $sheet->setCellValue('I2', 'Tax ID if type=company');
        $sheet->setCellValue('J2', 'Required if type=personal');
        $sheet->setCellValue('K2', 'Required if type=personal');
        $sheet->setCellValue('L2', 'Optional notes');
        $sheet->setCellValue('M2', 'lead, prospect, customer, or inactive');
        $sheet->setCellValue('N2', 'Comma separated tags (e.g. "vip, referral")');

        // Style the instruction row
        $sheet->getStyle('A2:N2')->applyFromArray([
            'font' => [
                'italic' => true,
                'color' => ['rgb' => '6B7280'], // Gray color
                'size' => 9,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'], // Light gray
            ],
        ]);

        // Add example data in row 3 and 4
        // Example 1: Company
        $sheet->setCellValue('A3', 'company');
        $sheet->setCellValue('B3', 'PT Contoh Indonesia');
        $sheet->setCellValue('C3', 'contact@contoh.co.id');
        $sheet->setCellValue('D3', '21123456');
        $sheet->setCellValue('E3', 'Indonesia');
        $sheet->setCellValue('F3', '+62');
        $sheet->setCellValue('G3', 'Jl. Sudirman No. 123, Jakarta');
        $sheet->setCellValue('H3', 'PT Contoh Indonesia');
        $sheet->setCellValue('I3', '01.234.567.8-901.000');
        $sheet->setCellValue('J3', '');
        $sheet->setCellValue('K3', '');
        $sheet->setCellValue('L3', 'Perusahaan besar di bidang teknologi');
        $sheet->setCellValue('M3', 'lead');
        $sheet->setCellValue('N3', 'tech, enterprise, jakarta');

        // Example 2: Personal
        $sheet->setCellValue('A4', 'personal');
        $sheet->setCellValue('B4', '');
        $sheet->setCellValue('C4', 'john.doe@email.com');
        $sheet->setCellValue('D4', '8123456789');
        $sheet->setCellValue('E4', 'Indonesia');
        $sheet->setCellValue('F4', '+62');
        $sheet->setCellValue('G4', 'Jl. Gatot Subroto No. 45, Bandung');
        $sheet->setCellValue('H4', '');
        $sheet->setCellValue('I4', '');
        $sheet->setCellValue('J4', 'John');
        $sheet->setCellValue('K4', 'Doe');
        $sheet->setCellValue('L4', 'Customer potensial dari referral');
        $sheet->setCellValue('M4', 'prospect');
        $sheet->setCellValue('N4', 'personal, referral');

        // Style example rows
        $sheet->getStyle('A3:N4')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEF3C7'], // Light yellow
            ],
        ]);

        // Set row height
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(30);

        return [];
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // type
            'B' => 30,  // name
            'C' => 25,  // email
            'D' => 18,  // phone
            'E' => 18,  // country
            'F' => 15,  // country_code
            'G' => 35,  // address
            'H' => 30,  // company_name
            'I' => 20,  // tax_id
            'J' => 15,  // first_name
            'K' => 15,  // last_name
            'L' => 40,  // notes
            'M' => 15,  // status
            'N' => 25,  // tags
        ];
    }
}
