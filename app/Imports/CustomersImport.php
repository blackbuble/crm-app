<?php
// app/Imports/CustomersImport.php
namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Str;

class CustomersImport implements OnEachRow, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure
{
    use SkipsFailures;

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();
        $type = strtolower($rowData['type'] ?? 'personal');
        $email = $rowData['email'] ?? null;
        $phone = $rowData['phone'] ?? null;

        // Skip if no identifiers? Or just key by email/phone?
        // Let's rely on email if present, else phone?
        // updateOrCreate needs a unique key.
        // If multiple keys (email OR phone), it's tricky.
        
        $customer = null;

        if ($email) {
            $customer = Customer::where('email', $email)->first();
        }
        if (!$customer && $phone) {
            $customer = Customer::where('phone', $phone)->first();
        }

        $data = [
            'type' => $type,
            'name' => $rowData['name'] ?? ($type === 'company' ? ($rowData['company_name'] ?? '-') : "{$rowData['first_name']} {$rowData['last_name']}"),
            'email' => $email,
            'phone' => $phone,
            'country' => $rowData['country'] ?? null,
            'country_code' => $rowData['country_code'] ?? null,
            'address' => $rowData['address'] ?? null,
            'company_name' => $type === 'company' ? ($rowData['company_name'] ?? null) : null,
            'tax_id' => $type === 'company' ? ($rowData['tax_id'] ?? null) : null,
            'first_name' => $type === 'personal' ? ($rowData['first_name'] ?? null) : null,
            'last_name' => $type === 'personal' ? ($rowData['last_name'] ?? null) : null,
            'notes' => $rowData['notes'] ?? null,
            // Only set status/assigned if creating? Or always?
            // Let's keep logic simple: update everything
            'assigned_to' => auth()->id(), // assign to uploader
        ];

        if ($customer) {
            // Update
            $customer->update($data);
        } else {
            // Create
            $data['status'] = $rowData['status'] ?? 'lead';
            $data['assigned_at'] = now();
            $customer = Customer::create($data);
        }

        // Tags
        if (!empty($rowData['tags'])) {
            $tags = array_map('trim', explode(',', $rowData['tags']));
            $customer->attachTags($tags); // Spatie tags
        }
    }

    public function rules(): array
    {
        return [
            'type' => 'nullable|in:company,personal', // nullable and default handled in code
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            // Removed 'unique' rule because we handle upsert
        ];
    }
}