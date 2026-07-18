<?php

namespace App\Http\Controllers\Donor;

use App\Http\Controllers\Controller;
use App\Models\DonationHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DonorDashboardController extends Controller
{
    /**
     * Prompt 14: Donor dashboard — donation history timeline + badge + stats.
     */
    public function index(): View
    {
        $user        = Auth::user();
        $profile     = $user->donorProfile;
        $history     = $user->donationHistory()
                            ->with('bloodRequest')
                            ->orderByDesc('donated_at')
                            ->get();

        // Compute average trust score (trust_score is updated by feedback)
        $avgRating = $history->whereNotNull('rating')->avg('rating');

        return view('donor.dashboard', [
            'user'      => $user,
            'profile'   => $profile,
            'history'   => $history,
            'avgRating' => $avgRating ? round($avgRating, 1) : null,
        ]);
    }

    /**
     * Prompt 14: Show the feedback form for a donation history record.
     * Only the requester of the linked blood request can submit feedback.
     */
    public function showFeedbackForm(DonationHistory $donationHistory): View|RedirectResponse
    {
        $this->authorizeRequester($donationHistory);

        if ($donationHistory->rating !== null) {
            return redirect()->back()->with('info', 'Feedback has already been submitted for this donation.');
        }

        return view('donor.feedback', ['donationHistory' => $donationHistory]);
    }

    /**
     * Prompt 14: Store post-donation feedback (1–5 rating + optional notes).
     * Updates the donor's trust_score to the running average of their ratings.
     */
    public function submitFeedback(Request $request, DonationHistory $donationHistory): RedirectResponse
    {
        $this->authorizeRequester($donationHistory);

        if ($donationHistory->rating !== null) {
            return redirect()->back()->with('info', 'Feedback already submitted.');
        }

        $validated = $request->validate([
            'rating'         => ['required', 'integer', 'min:1', 'max:5'],
            'feedback_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $donationHistory->update([
            'rating'         => $validated['rating'],
            'feedback_notes' => $validated['feedback_notes'] ?? null,
        ]);

        // Recalculate the donor's trust_score as the avg of all their ratings
        $donorProfile = $donationHistory->donor->donorProfile;
        if ($donorProfile) {
            $avgRating = DonationHistory::where('donor_id', $donorProfile->user_id)
                ->whereNotNull('rating')
                ->avg('rating');

            $donorProfile->update([
                'trust_score' => $avgRating ? round($avgRating, 2) : 0,
            ]);
        }

        return redirect()
            ->route('donor.dashboard')
            ->with('success', 'Thank you for your feedback!');
    }

    /**
     * Ensure the current user is the requester of the linked blood request.
     */
    protected function authorizeRequester(DonationHistory $donationHistory): void
    {
        $user = Auth::user();

        // If there's a linked blood request, only the requester can give feedback
        if ($donationHistory->blood_request_id && $donationHistory->bloodRequest) {
            $requesterId = $donationHistory->bloodRequest->requester_id;
            if ($requesterId && $requesterId !== $user->id) {
                abort(403, 'You are not authorized to submit feedback for this donation.');
            }
        }
    }
}
