<?php

namespace App\Http\Controllers\Donor;

use App\Helpers\BangladeshDistricts;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteDonorProfileRequest;
use App\Models\DonorProfile;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonorProfileController extends Controller
{
    public function __construct(
        protected OtpService $otpService,
    ) {}

    /**
     * Show the "Complete Your Donor Profile" form.
     */
    public function create(): View|RedirectResponse
    {
        $user = auth()->user();

        // If profile already exists and is verified, go to dashboard
        if ($user->donorProfile?->is_verified) {
            return redirect()->route('dashboard')
                ->with('info', 'Your donor profile is already complete and verified.');
        }

        // If profile exists but not verified, go to OTP page
        if ($user->donorProfile && ! $user->donorProfile->is_verified) {
            return redirect()->route('donor.otp.show');
        }

        return view('donor.complete-profile', [
            'districts'   => BangladeshDistricts::all(),
            'bloodGroups' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
        ]);
    }

    /**
     * Store the donor profile and send OTP for phone verification.
     */
    public function store(CompleteDonorProfileRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $validated = $request->validated();

        // Create or update the donor profile
        $profile = DonorProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'blood_group'        => $validated['blood_group'],
                'district'           => $validated['district'],
                'phone'              => $validated['phone'],
                'last_donation_date' => $validated['last_donation_date'] ?? null,
                'is_available'       => true,
                'is_verified'        => false,
            ]
        );

        // If last_donation_date was provided and is within 90 days, apply cooldown
        if ($profile->last_donation_date && $profile->days_since_last_donation < 90) {
            $profile->update(['is_available' => false]);
        }

        // Generate and send OTP
        $otp = $this->otpService->generate($user, $validated['phone']);

        return redirect()->route('donor.otp.show')
            ->with('status', 'A verification code has been sent to your email.')
            ->with('otp_debug', app()->environment('local') ? $otp->code : null);
    }

    /**
     * Show the OTP verification form.
     */
    public function showOtpForm(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user->donorProfile) {
            return redirect()->route('donor.profile.create');
        }

        if ($user->donorProfile->is_verified) {
            return redirect()->route('dashboard')
                ->with('info', 'Your phone number is already verified.');
        }

        return view('donor.verify-otp', [
            'phone' => $user->donorProfile->masked_phone,
        ]);
    }

    /**
     * Verify the OTP code.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = auth()->user();
        $verified = $this->otpService->verify($user, $request->code);

        if (! $verified) {
            return back()->withErrors([
                'code' => 'The verification code is invalid or has expired. Please try again.',
            ]);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Your phone number has been verified! Your donor profile is now active.');
    }

    /**
     * Resend the OTP code.
     */
    public function resendOtp(): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->donorProfile) {
            return redirect()->route('donor.profile.create');
        }

        if ($user->donorProfile->is_verified) {
            return redirect()->route('dashboard')
                ->with('info', 'Your phone number is already verified.');
        }

        $otp = $this->otpService->generate($user, $user->donorProfile->phone);

        return back()
            ->with('status', 'A new verification code has been sent to your email.')
            ->with('otp_debug', app()->environment('local') ? $otp->code : null);
    }
}
