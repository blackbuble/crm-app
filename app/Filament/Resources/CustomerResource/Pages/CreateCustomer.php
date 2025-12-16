<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $email = $data['email'] ?? null;
        $phone = $data['phone'] ?? null;

        // If no unique identifiers, proceed normally (allows duplicate names if desired)
        if (! $email && ! $phone) {
             return static::getModel()::create($data);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($data, $email, $phone) {
             // Create a lock key based on identifiers
             $lockKey = 'customer_create_' . md5(
                 ($email ? strtolower(trim($email)) : '') . '|' . 
                 ($phone ? trim($phone) : '')
             );
             
             $acquired = \Illuminate\Support\Facades\DB::scalar("SELECT GET_LOCK(?, 5)", [$lockKey]);
             
             if (! $acquired) {
                 throw new \Exception('System busy checking duplicates. Please try again.');
             }

             try {
                 $query = \App\Models\Customer::query();
                 $hasCondition = false;
                 
                 if ($email) { 
                    $query->where('email', $email); 
                    $hasCondition = true;
                 }
                 
                 if ($phone) {
                    if ($hasCondition) $query->orWhere('phone', $phone);
                    else $query->where('phone', $phone);
                 }
                 
                 if ($query->exists()) {
                      throw \Illuminate\Validation\ValidationException::withMessages([
                          'email' => 'Customer with this email or phone already exists.',
                      ]);
                 }
                 
                 return static::getModel()::create($data);
             } finally {
                 \Illuminate\Support\Facades\DB::scalar("SELECT RELEASE_LOCK(?)", [$lockKey]);
             }
        });
    }
}
