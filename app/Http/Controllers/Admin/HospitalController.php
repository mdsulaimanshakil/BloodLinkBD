<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\BangladeshDistricts;
use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HospitalController extends Controller
{
    /**
     * Prompt 15: List all hospitals/blood banks for admin management.
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

        $hospitals = $query->paginate(25)->withQueryString();

        return view('admin.hospitals.index', [
            'hospitals' => $hospitals,
            'districts' => BangladeshDistricts::all(),
            'filters'   => ['district' => $request->district, 'type' => $request->type],
        ]);
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('admin.hospitals.create', [
            'districts' => BangladeshDistricts::all(),
        ]);
    }

    /**
     * Store a new hospital.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:200'],
            'district'  => ['required', 'string', 'max:100'],
            'address'   => ['nullable', 'string', 'max:500'],
            'contact'   => ['nullable', 'string', 'max:20'],
            'type'      => ['required', 'in:hospital,blood_bank'],
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        Hospital::create($validated);

        return redirect()
            ->route('admin.hospitals.index')
            ->with('success', 'Hospital added successfully!');
    }

    /**
     * Show the edit form.
     */
    public function edit(Hospital $hospital): View
    {
        return view('admin.hospitals.edit', [
            'hospital'  => $hospital,
            'districts' => BangladeshDistricts::all(),
        ]);
    }

    /**
     * Update a hospital.
     */
    public function update(Request $request, Hospital $hospital): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:200'],
            'district'  => ['required', 'string', 'max:100'],
            'address'   => ['nullable', 'string', 'max:500'],
            'contact'   => ['nullable', 'string', 'max:20'],
            'type'      => ['required', 'in:hospital,blood_bank'],
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $hospital->update($validated);

        return redirect()
            ->route('admin.hospitals.index')
            ->with('success', 'Hospital updated successfully!');
    }

    /**
     * Delete a hospital.
     */
    public function destroy(Hospital $hospital): RedirectResponse
    {
        $hospital->delete();

        return redirect()
            ->route('admin.hospitals.index')
            ->with('success', 'Hospital deleted.');
    }
}
