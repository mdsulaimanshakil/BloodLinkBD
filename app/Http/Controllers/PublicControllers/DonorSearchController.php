<?php

namespace App\Http\Controllers\PublicControllers;

use App\Helpers\BangladeshDistricts;
use App\Http\Controllers\Controller;
use App\Models\DonorProfile;
use App\Services\Notifications\WhatsAppNotificationChannel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonorSearchController extends Controller
{
    /**
     * Show the public donor search page with filters.
     */
    public function index(Request $request): View
    {
        $query = DonorProfile::query()
            ->where('is_verified', true)
            ->where('is_available', true)
            ->with('user:id,name');

        // Apply filters
        if ($request->filled('blood_group')) {
            $query->bloodGroup($request->blood_group);
        }

        if ($request->filled('district')) {
            $query->district($request->district);
        }

        $donors = $query->orderByDesc('donation_count')
            ->orderByDesc('trust_score')
            ->paginate(12)
            ->withQueryString();

        // Determine if the current user can see full phone numbers
        $canRevealPhone = $this->canRevealPhone($request);

        return view('public.donor-search', [
            'donors'         => $donors,
            'districts'      => BangladeshDistricts::all(),
            'bloodGroups'    => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
            'canRevealPhone' => $canRevealPhone,
            'filters'        => [
                'blood_group' => $request->blood_group,
                'district'    => $request->district,
            ],
        ]);
    }

    /**
     * Determine if the current user is allowed to see full phone numbers.
     *
     * Full phone + WhatsApp link are revealed only if:
     * - User is logged in AND has a verified donor profile, OR
     * - User is an admin.
     */
    protected function canRevealPhone(Request $request): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->donorProfile?->is_verified ?? false;
    }
}
