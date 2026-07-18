<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\BloodRequest;
use App\Models\DonationHistory;
use App\Models\DonorProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Prompt 16: Main admin dashboard — stats overview.
     */
    public function index(): View
    {
        // ── Platform Stats ────────────────────────────────────
        $totalDonors          = DonorProfile::count();
        $verifiedDonors       = DonorProfile::where('is_verified', true)->count();
        $pendingVerification  = DonorProfile::where('is_verified', false)->count();
        $availableDonors      = DonorProfile::where('is_available', true)->where('is_verified', true)->count();

        $requestsByStatus = BloodRequest::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalRequests   = BloodRequest::count();
        $activeRequests  = $requestsByStatus->get('active', 0);
        $expiredRequests = $requestsByStatus->get('expired', 0);
        $removedRequests = $requestsByStatus->get('removed', 0);

        $donationsThisMonth = DonationHistory::whereMonth('donated_at', now()->month)
            ->whereYear('donated_at', now()->year)
            ->count();

        // ── Recent Admin Activity ─────────────────────────────
        $recentAuditLogs = AdminAuditLog::with('admin')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalDonors',
            'verifiedDonors',
            'pendingVerification',
            'availableDonors',
            'totalRequests',
            'activeRequests',
            'expiredRequests',
            'removedRequests',
            'donationsThisMonth',
            'recentAuditLogs',
        ));
    }

    // ══════════════════════════════════════════════════════════════
    // Donor Verification Queue
    // ══════════════════════════════════════════════════════════════

    /**
     * Prompt 16: List all pending (unverified) donor profiles for admin review.
     */
    public function pendingDonors(Request $request): View
    {
        $donors = DonorProfile::with('user')
            ->where('is_verified', false)
            ->orderBy('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.donors.pending', compact('donors'));
    }

    /**
     * Prompt 16: Approve (verify) a donor profile.
     * Writes action to admin_audit_logs.
     */
    public function verifyDonor(DonorProfile $donorProfile): RedirectResponse
    {
        $donorProfile->update(['is_verified' => true, 'is_available' => true]);

        AdminAuditLog::create([
            'admin_id'    => Auth::id(),
            'action'      => 'verify_donor',
            'target_type' => DonorProfile::class,
            'target_id'   => $donorProfile->id,
            'notes'       => "Approved donor profile for user #{$donorProfile->user_id} ({$donorProfile->user?->name})",
        ]);

        return redirect()
            ->route('admin.donors.pending')
            ->with('success', "Donor {$donorProfile->user?->name} has been verified.");
    }

    /**
     * Prompt 16: Reject / remove a donor profile (marks as not verified).
     * Writes action to admin_audit_logs.
     */
    public function rejectDonor(Request $request, DonorProfile $donorProfile): RedirectResponse
    {
        $request->validate(['notes' => ['nullable', 'string', 'max:500']]);

        $donorProfile->update(['is_verified' => false, 'is_available' => false]);

        AdminAuditLog::create([
            'admin_id'    => Auth::id(),
            'action'      => 'reject_donor',
            'target_type' => DonorProfile::class,
            'target_id'   => $donorProfile->id,
            'notes'       => $request->notes ?? "Rejected donor profile for user #{$donorProfile->user_id} ({$donorProfile->user?->name})",
        ]);

        return redirect()
            ->route('admin.donors.pending')
            ->with('success', "Donor {$donorProfile->user?->name} has been rejected.");
    }

    // ══════════════════════════════════════════════════════════════
    // Blood Request Management
    // ══════════════════════════════════════════════════════════════

    /**
     * Prompt 16: List all active/fake blood requests for admin review.
     */
    public function requests(Request $request): View
    {
        $query = BloodRequest::with('requester')
            ->orderByRaw("FIELD(status, 'active', 'expired', 'removed', 'fulfilled')")
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(25)->withQueryString();

        return view('admin.requests.index', compact('requests'));
    }

    /**
     * Prompt 16: Remove (mark as removed) a blood request deemed fake/invalid.
     * Writes action to admin_audit_logs.
     */
    public function removeRequest(Request $request, BloodRequest $bloodRequest): RedirectResponse
    {
        $request->validate(['notes' => ['nullable', 'string', 'max:500']]);

        $bloodRequest->update(['status' => 'removed']);

        AdminAuditLog::create([
            'admin_id'    => Auth::id(),
            'action'      => 'remove_request',
            'target_type' => BloodRequest::class,
            'target_id'   => $bloodRequest->id,
            'notes'       => $request->notes ?? "Removed blood request #{$bloodRequest->id} ({$bloodRequest->blood_group} in {$bloodRequest->district})",
        ]);

        return redirect()
            ->route('admin.requests.index')
            ->with('success', "Request #{$bloodRequest->id} has been removed.");
    }

    /**
     * Prompt 16: Restore a previously removed request back to active.
     * Writes action to admin_audit_logs.
     */
    public function restoreRequest(BloodRequest $bloodRequest): RedirectResponse
    {
        $bloodRequest->update(['status' => 'active']);

        AdminAuditLog::create([
            'admin_id'    => Auth::id(),
            'action'      => 'restore_request',
            'target_type' => BloodRequest::class,
            'target_id'   => $bloodRequest->id,
            'notes'       => "Restored blood request #{$bloodRequest->id} ({$bloodRequest->blood_group} in {$bloodRequest->district})",
        ]);

        return redirect()
            ->route('admin.requests.index')
            ->with('success', "Request #{$bloodRequest->id} has been restored.");
    }

    // ══════════════════════════════════════════════════════════════
    // Audit Log Viewer
    // ══════════════════════════════════════════════════════════════

    /**
     * Prompt 16: View the full admin audit log, newest first.
     */
    public function auditLog(Request $request): View
    {
        $query = AdminAuditLog::with('admin')
            ->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        $logs   = $query->paginate(30)->withQueryString();
        $admins = User::where('role', 'admin')->orderBy('name')->get();

        return view('admin.audit-log', compact('logs', 'admins'));
    }
}
