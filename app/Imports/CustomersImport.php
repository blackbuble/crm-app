<?php
// app/Imports/CustomersImport.php
namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Str;

class CustomersImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure
{
    use SkipsFailures;

    public function model(array $row)
    {
        $type = strtolower($row['type'] ?? 'personal');
        
        return new Customer([
            'type' => $type,
            'name' => $row['name'] ?? ($type === 'company' ? $row['company_name'] : "{$row['first_name']} {$row['last_name']}"),
            'email' => $row['email'] ?? null,
            'phone' => $row['phone'] ?? null,
            'address' => $row['address'] ?? null,
            'company_name' => $type === 'company' ? ($row['company_name'] ?? null) : null,
            'tax_id' => $type === 'company' ? ($row['tax_id'] ?? null) : null,
            'first_name' => $type === 'personal' ? ($row['first_name'] ?? null) : null,
            'last_name' => $type === 'personal' ? ($row['last_name'] ?? null) : null,
            'notes' => $row['notes'] ?? null,
            'status' => $row['status'] ?? 'lead',
        ]);
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:company,personal',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            '*.email' => 'nullable|email|unique:customers,email',
        ];
    }
}