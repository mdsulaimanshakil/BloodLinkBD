<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\BangladeshDistricts;
use App\Http\Controllers\Controller;
use App\Http\Resources\DonorResource;
use App\Models\DonorProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Prompt 18: API – Donor Search.
 *
 * GET /api/v1/donors?blood_group=A%2B&district=Dhaka
 *
 * Public — no auth required. Returns verified, available donors.
 * Phone is masked unless caller is an authenticated verified donor or admin.
 */
class DonorSearchApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'blood_group' => ['nullable', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'district'    => ['nullable', 'string', 'in:' . implode(',', BangladeshDistricts::all())],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = DonorProfile::query()
            ->where('is_verified', true)
            ->where('is_available', true)
            ->with('user:id,name');

        if ($request->filled('blood_group')) {
            $query->bloodGroup($request->blood_group);
        }

        if ($request->filled('district')) {
            $query->district($request->district);
        }

        $donors = $query
            ->orderByDesc('donation_count')
            ->orderByDesc('trust_score')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return DonorResource::collection($donors);
    }
}
