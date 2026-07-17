<?php

namespace App\Http\Controllers\PublicControllers;

use App\Helpers\BangladeshDistricts;
use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveFeedController extends Controller
{
    /**
     * Show the live request feed page.
     */
    public function index(Request $request): View
    {
        $requests = $this->getFilteredRequests($request);

        return view('public.live-feed', [
            'requests'    => $requests,
            'districts'   => BangladeshDistricts::all(),
            'bloodGroups' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
            'filters'     => [
                'blood_group' => $request->blood_group,
                'district'    => $request->district,
            ],
        ]);
    }

    /**
     * JSON endpoint for auto-refresh polling (every 30 seconds).
     */
    public function poll(Request $request): JsonResponse
    {
        $requests = $this->getFilteredRequests($request);

        return response()->json([
            'html'  => view('public.partials.feed-cards', ['requests' => $requests])->render(),
            'count' => $requests->total(),
        ]);
    }

    /**
     * Build the filtered, paginated query for active requests.
     */
    protected function getFilteredRequests(Request $request)
    {
        $query = BloodRequest::query()
            ->active()
            ->byUrgencyThenRecent();

        if ($request->filled('blood_group')) {
            $query->bloodGroup($request->blood_group);
        }

        if ($request->filled('district')) {
            $query->district($request->district);
        }

        return $query->paginate(15)->withQueryString();
    }
}
