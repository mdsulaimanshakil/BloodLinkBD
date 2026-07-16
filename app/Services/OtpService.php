<?php

namespace App\Services;

use App\Models\DonorProfile;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    /**
     * OTP validity period in minutes.
     */
    protected const EXPIRY_MINUTES = 10;

    /**
     * Generate a new 6-digit OTP for the given user and phone number.
     *
     * Invalidates any previous unused OTPs for this user.
     */
    public function generate(User $user, string $phone): OtpCode
    {
        // Invalidate any previous valid OTPs for this user
        OtpCode::where('user_id', $user->id)
            ->valid()
            ->update(['expires_at' => now()]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $otp = OtpCode::create([
            'user_id'    => $user->id,
            'code'       => $code,
            'phone'      => $phone,
            'expires_at' => Carbon::now()->addMinutes(self::EXPIRY_MINUTES),
        ]);

        // Send the OTP via email (free channel)
        $this->sendViaEmail($user, $code);

        return $otp;
    }

    /**
     * Verify an OTP code for the given user.
     *
     * On success: marks OTP as verified, sets donor profile is_verified = true.
     *
     * @return bool Whether the OTP was valid.
     */
    public function verify(User $user, string $code): bool
    {
        $otp = OtpCode::where('user_id', $user->id)
            ->where('code', $code)
            ->valid()
            ->latest()
            ->first();

        if (! $otp) {
            return false;
        }

        // Mark OTP as verified
        $otp->update(['verified_at' => now()]);

        // Mark donor profile as verified
        $donorProfile = DonorProfile::where('user_id', $user->id)->first();

        if ($donorProfile) {
            $donorProfile->update(['is_verified' => true]);
        }

        return true;
    }

    /**
     * Send the OTP via email (free channel).
     */
    protected function sendViaEmail(User $user, string $code): void
    {
        try {
            Mail::raw(
                "Your BloodLinkBD verification code is: {$code}\n\nThis code expires in " . self::EXPIRY_MINUTES . " minutes.\n\nIf you did not request this, please ignore this email.",
                function ($mail) use ($user) {
                    $mail->to($user->email)
                         ->subject('BloodLinkBD — Phone Verification Code');
                }
            );
        } catch (\Exception $e) {
            Log::warning("OtpService: Failed to send OTP email to {$user->email}: " . $e->getMessage());
        }
    }
}
