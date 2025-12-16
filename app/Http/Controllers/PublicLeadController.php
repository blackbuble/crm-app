<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicLeadController extends Controller
{
    public function showForm(Request $request)
    {
        // Capture all query parameters to pass to the view
        $trackingData = $request->only([
            'utm_source', 'utm_medium', 'utm_campaign', 
            'utm_term', 'utm_content', 'gclid', 'fbclid',
            'gad_source', 'gad_campaignid', 'gbraid', 'wbraid'
        ]);
        
        // Map gad_campaignid to gad_campaign for storage
        if (isset($trackingData['gad_campaignid'])) {
            $trackingData['gad_campaign'] = $trackingData['gad_campaignid'];
            unset($trackingData['gad_campaignid']);
        }
        
        return view('public.lead-form', compact('trackingData'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $data['status'] = 'lead';
        $data['source'] = $data['utm_source'] ?? 'Web Form'; // Default source
        $data['type'] = !empty($data['company_name']) ? 'company' : 'personal';

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
                // Prevent race conditions with named lock
                $lockKey = 'public_lead_' . md5(strtolower(trim($data['email'])));
                $acquired = \Illuminate\Support\Facades\DB::scalar("SELECT GET_LOCK(?, 5)", [$lockKey]);
                
                if (!$acquired) {
                    throw new \Exception('System busy');
                }
                
                try {
                    // Use updateOrCreate to handle duplicates gracefully
                    // If customer exists, we might want to update or just leave it. 
                    // Here we update to capture latest info.
                    Customer::updateOrCreate(
                        ['email' => $data['email']],
                        $data
                    );
                } finally {
                    \Illuminate\Support\Facades\DB::scalar("SELECT RELEASE_LOCK(?)", [$lockKey]);
                }
            });
        } catch (\Exception $e) {
            // Log error but show success to user to prevent enumeration/confusion? 
            // Or show generic error.
            if ($e->getMessage() !== 'System busy') {
                 \Illuminate\Support\Facades\Log::error('Lead Gen Error: ' . $e->getMessage());
            }
            // For now, let's assume if it fails, we might want to tell the user to retry if it's a lock issue
            if ($e->getMessage() === 'System busy') {
                 return back()->with('error', 'System is busy, please try again.')->withInput();
            }
        }

        return back()->with('success', 'Thank you! We will contact you shortly.');
    }
}
