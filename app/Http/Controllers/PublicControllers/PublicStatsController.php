<?php

namespace App\Http\Controllers\PublicControllers;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\DonationHistory;
use App\Models\DonorProfile;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PublicStatsController extends Controller
{
    /**
     * Prompt 17: Public stats page — total donors, fulfilled requests, lives helped this month.
     * All queries are cached for 10 minutes to stay lightweight.
     */
    public function index(): View
    {
        $stats = Cache::remember('public_stats', 600, function () {
            // ── Total verified donors on platform ──────────────────
            $totalDonors = DonorProfile::where('is_verified', true)->count();

            // ── Total available donors right now ───────────────────
            $availableDonors = DonorProfile::where('is_verified', true)
                ->where('is_available', true)
                ->count();

            // ── Total requests fulfilled (status = fulfilled) ──────
            $totalFulfilled = BloodRequest::where('status', 'fulfilled')->count();

            // ── Lives helped this month: donations recorded in current month ─
            $livesThisMonth = DonationHistory::whereMonth('donated_at', now()->month)
                ->whereYear('donated_at', now()->year)
                ->count();

            // ── Lives helped this year ─────────────────────────────
            $livesThisYear = DonationHistory::whereYear('donated_at', now()->year)->count();

            // ── Total all-time donations ───────────────────────────
            $totalDonations = DonationHistory::count();

            // ── Active requests right now ──────────────────────────
            $activeRequests = BloodRequest::where('status', 'active')->count();

            // ── Critical requests active right now ─────────────────
            $criticalRequests = BloodRequest::where('status', 'active')
                ->where('urgency', 'critical')
                ->count();

            // ── Breakdown by blood group (verified donors) ─────────
            $byBloodGroup = DonorProfile::where('is_verified', true)
                ->selectRaw('blood_group, count(*) as total')
                ->groupBy('blood_group')
                ->orderByDesc('total')
                ->pluck('total', 'blood_group');

            // ── Top 5 districts by donor count ─────────────────────
            $topDistricts = DonorProfile::where('is_verified', true)
                ->selectRaw('district, count(*) as total')
                ->groupBy('district')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            // ── Monthly donation trend (last 6 months) ─────────────
            $monthlyTrend = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $monthlyTrend[] = [
                    'label' => $month->format('M Y'),
                    'count' => DonationHistory::whereMonth('donated_at', $month->month)
                        ->whereYear('donated_at', $month->year)
                        ->count(),
                ];
            }

            return compact(
                'totalDonors',
                'availableDonors',
                'totalFulfilled',
                'livesThisMonth',
                'livesThisYear',
                'totalDonations',
                'activeRequests',
                'criticalRequests',
                'byBloodGroup',
                'topDistricts',
                'monthlyTrend',
            );
        });

        return view('public.stats', $stats);
    }
}
