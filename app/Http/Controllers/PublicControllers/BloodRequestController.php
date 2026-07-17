<?php

namespace App\Http\Controllers\PublicControllers;

use App\Helpers\BangladeshDistricts;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBloodRequestRequest;
use App\Models\BloodRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BloodRequestController extends Controller
{
    /**
     * Show the public emergency blood request form.
     */
    public function create(): View
    {
        return view('public.blood-request-form', [
            'districts'   => BangladeshDistricts::all(),
            'bloodGroups' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
            'urgencies'   => ['normal' => 'Normal', 'urgent' => 'Urgent', 'critical' => 'Critical'],
            'recaptchaSiteKey' => config('services.recaptcha.site_key'),
        ]);
    }

    /**
     * Store a new public blood request.
     */
    public function store(StoreBloodRequestRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Attach the authenticated user as requester if logged in
        if ($request->user()) {
            $validated['requester_id'] = $request->user()->id;
        }

        // Remove reCAPTCHA response from data (not a model field)
        unset($validated['g-recaptcha-response']);

        $bloodRequest = BloodRequest::create($validated);

        return redirect()->route('blood-requests.success', $bloodRequest)
            ->with('success', 'Your emergency blood request has been posted successfully!');
    }

    /**
     * Show the success/confirmation page after posting a request.
     */
    public function success(BloodRequest $bloodRequest): View
    {
        return view('public.blood-request-success', [
            'bloodRequest' => $bloodRequest,
        ]);
    }
}
