<?php

namespace App\Http\Controllers\Public;

use App\Helpers\BangladeshDistricts;
use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HospitalDirectoryController extends Controller
{
    /**
     * Prompt 15: Public hospital & blood bank directory with district filter + map data.
     */
    public function index(Request $request): View
    {
        $query = Hospital::query()->orderBy('district')->orderBy('name');

        if ($request->filled('district')) {
            $query->district($request->district);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $hospitals = $query->paginate(20)->withQueryString();

        // All hospitals with coordinates for the Leaflet.js map
        $mapData = Hospital::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($request->filled('district'), fn($q) => $q->where('district', $request->district))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->get(['id', 'name', 'district', 'address', 'contact', 'type', 'latitude', 'longitude']);

        return view('public.hospitals', [
            'hospitals' => $hospitals,
            'mapData'   => $mapData,
            'districts' => BangladeshDistricts::all(),
            'filters'   => [
                'district' => $request->district,
                'type'     => $request->type,
            ],
        ]);
    }

    /**
     * Show a single hospital's detail page.
     */
    public function show(Hospital $hospital): View
    {
        return view('public.hospital-detail', [
            'hospital' => $hospital,
        ]);
    }
}
