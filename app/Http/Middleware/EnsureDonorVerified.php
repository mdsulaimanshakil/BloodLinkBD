<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks unverified donors from performing protected actions
 * (e.g. responding to blood requests).
 *
 * Checks that the authenticated user has a DonorProfile with is_verified = true.
 */
class EnsureDonorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $profile = $user->donorProfile;

        // No profile at all — redirect to complete it
        if (! $profile) {
            return redirect()->route('donor.profile.create')
                ->with('warning', 'Please complete your donor profile first.');
        }

        // Profile exists but not verified — redirect to OTP verification
        if (! $profile->is_verified) {
            return redirect()->route('donor.otp.show')
                ->with('warning', 'Please verify your phone number before continuing.');
        }

        return $next($request);
    }
}
