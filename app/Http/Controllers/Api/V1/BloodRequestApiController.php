<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\BangladeshDistricts;
use App\Http\Controllers\Controller;
use App\Http\Resources\BloodRequestResource;
use App\Jobs\NotifyDonorsJob;
use App\Models\BloodRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

/**
 * Prompt 18: API – Request Feed + Request Creation.
 *
 * GET  /api/v1/requests          — live feed of active requests (paginated, filterable)
 * GET  /api/v1/requests/{id}     — single request detail
 * POST /api/v1/requests          — create a new blood request (requires auth:sanctum)
 */
class BloodRequestApiController extends Controller
{
    /**
     * Live feed of active blood requests.
     * Public — no auth required.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'blood_group' => ['nullable', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'district'    => ['nullable', 'string'],
            'urgency'     => ['nullable', 'string', 'in:normal,urgent,critical'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = BloodRequest::query()
            ->active()
            ->byUrgencyThenRecent()
            ->withCount('donorResponses');

        if ($request->filled('blood_group')) {
            $query->bloodGroup($request->blood_group);
        }

        if ($request->filled('district')) {
            $query->district($request->district);
        }

        if ($request->filled('urgency')) {
            $query->where('urgency', $request->urgency);
        }

        $requests = $query
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return BloodRequestResource::collection($requests);
    }

    /**
     * Show a single blood request.
     * Public — no auth required.
     */
    public function show(BloodRequest $bloodRequest): BloodRequestResource
    {
        $bloodRequest->loadCount('donorResponses');

        return new BloodRequestResource($bloodRequest);
    }

    /**
     * Create a new blood request.
     * Requires auth:sanctum — token must be provided.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_name'     => ['required', 'string', 'max:255'],
            'blood_group'      => ['required', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'district'         => ['required', 'string', Rule::in(BangladeshDistricts::all())],
            'hospital'         => ['required', 'string', 'max:255'],
            'urgency'          => ['required', Rule::in(['normal', 'urgent', 'critical'])],
            'requester_phone'  => ['required', 'string', 'regex:/^01[3-9]\d{8}$/'],
            'additional_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Rate-limit: max 3 active requests per phone number
        $activeCount = BloodRequest::where('requester_phone', $validated['requester_phone'])
            ->where('status', 'active')
            ->count();

        if ($activeCount >= 3) {
            return response()->json([
                'message' => 'You already have 3 active blood requests. Please wait for existing requests to expire.',
                'errors'  => ['requester_phone' => ['Rate limit: max 3 active requests per phone.']],
            ], 422);
        }

        // Attach the authenticated user as requester
        $validated['requester_id'] = $request->user()->id;

        $bloodRequest = BloodRequest::create($validated);

        // Dispatch queued notification job — same as the web flow
        NotifyDonorsJob::dispatch($bloodRequest);

        return response()->json(new BloodRequestResource($bloodRequest), 201);
    }
}
