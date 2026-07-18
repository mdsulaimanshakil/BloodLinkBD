<?php

namespace App\Http\Controllers\PublicControllers;

use App\Helpers\BangladeshDistricts;
use App\Helpers\BloodCompatibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBloodRequestRequest;
use App\Jobs\NotifyDonorsJob;
use App\Models\BloodRequest;
use App\Services\Notifications\WhatsAppNotificationChannel;
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
     * Store a new public blood request and dispatch donor notifications.
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

        // Dispatch queued job to notify eligible donors
        NotifyDonorsJob::dispatch($bloodRequest);

        return redirect()->route('blood-requests.success', $bloodRequest)
            ->with('success', 'Your emergency blood request has been posted successfully!');
    }

    /**
     * Show the request detail page with blood compatibility chart.
     */
    public function show(BloodRequest $bloodRequest): View
    {
        $compatibleDonors = BloodCompatibility::compatibleDonors($bloodRequest->blood_group);

        $whatsappLink = WhatsAppNotificationChannel::whatsappLink(
            $bloodRequest->requester_phone,
            "Hi, I saw your blood request for {$bloodRequest->blood_group} on BloodLinkBD. I'm available to donate."
        );

        // Check if the logged-in donor has already responded to this request
        $alreadyResponded = false;
        if (auth()->check()) {
            $alreadyResponded = $bloodRequest->donorResponses()
                ->where('donor_id', auth()->id())
                ->exists();
        }

        return view('public.blood-request-detail', [
            'bloodRequest'        => $bloodRequest,
            'compatibleDonors'    => $compatibleDonors,
            'compatibilityMatrix' => BloodCompatibility::matrix(),
            'bloodGroups'         => BloodCompatibility::BLOOD_GROUPS,
            'whatsappLink'        => $whatsappLink,
            'alreadyResponded'    => $alreadyResponded,
        ]);
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

