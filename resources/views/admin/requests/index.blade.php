<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Blood Requests — Admin | {{ config('app.name') }}</title>
    <meta name="description" content="Admin management of all blood requests — remove fake or invalid requests.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Figtree', sans-serif; background: #0a0f1e; color: #e2e8f0; min-height: 100vh; }
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 240px; flex-shrink: 0; background: rgba(255,255,255,0.03); border-right: 1px solid rgba(255,255,255,0.07); display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; overflow-y: auto; }
        .sidebar-brand { padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,0.07); }
        .sidebar-brand-name { font-size: 1.1rem; font-weight: 800; background: linear-gradient(135deg, #dc2626, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .sidebar-brand-sub { font-size: 0.7rem; color: #64748b; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.08em; }
        .sidebar-nav { flex: 1; padding: 16px 12px; }
        .nav-section-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #475569; padding: 12px 8px 4px; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 8px; color: #94a3b8; text-decoration: none; font-size: 0.875rem; transition: all 0.15s; margin-bottom: 2px; }
        .nav-item:hover { background: rgba(255,255,255,0.06); color: #e2e8f0; }
        .nav-item.active { background: rgba(220,38,38,0.15); color: #fca5a5; border: 1px solid rgba(220,38,38,0.2); }
        .nav-item .icon { font-size: 1rem; width: 20px; text-align: center; }
        .sidebar-footer { padding: 16px; border-top: 1px solid rgba(255,255,255,0.07); font-size: 0.75rem; color: #475569; }

        .main-content { flex: 1; overflow-x: hidden; }
        .topbar { background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.07); padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
        .topbar-title { font-size: 1.25rem; font-weight: 700; color: white; }
        .page-body { padding: 32px; }

        .filter-row { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 24px; }
        .filter-select { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #e2e8f0; border-radius: 8px; padding: 8px 12px; font-size: 0.875rem; }
        .filter-select option { background: #1e293b; }
        .filter-btn { padding: 8px 16px; border-radius: 8px; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.15s; }
        .filter-btn-primary { background: linear-gradient(135deg,#dc2626,#b91c1c); color: white; }
        .filter-btn-ghost { background: rgba(255,255,255,0.06); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); text-decoration: none; }

        .request-table { width: 100%; border-collapse: collapse; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; overflow: hidden; }
        .request-table th { padding: 12px 16px; text-align: left; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; border-bottom: 1px solid rgba(255,255,255,0.07); }
        .request-table td { padding: 12px 16px; font-size: 0.875rem; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
        .request-table tr:last-child td { border-bottom: none; }
        .request-table tr:hover td { background: rgba(255,255,255,0.02); }

        .urgency-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 100px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .urgency-critical { background: rgba(239,68,68,0.25); color: #fca5a5; border: 1px solid rgba(239,68,68,0.4); }
        .urgency-urgent   { background: rgba(245,158,11,0.2); color: #fcd34d; border: 1px solid rgba(245,158,11,0.35); }
        .urgency-normal   { background: rgba(100,116,139,0.2); color: #94a3b8; border: 1px solid rgba(100,116,139,0.3); }

        .status-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 100px; font-size: 0.7rem; font-weight: 700; }
        .status-active   { background: rgba(5,150,105,0.2);  color: #6ee7b7;  border: 1px solid rgba(5,150,105,0.3); }
        .status-expired  { background: rgba(100,116,139,0.2);color: #94a3b8;  border: 1px solid rgba(100,116,139,0.3); }
        .status-removed  { background: rgba(239,68,68,0.15); color: #fca5a5;  border: 1px solid rgba(239,68,68,0.25); }
        .status-fulfilled{ background: rgba(99,102,241,0.2); color: #a5b4fc;  border: 1px solid rgba(99,102,241,0.3); }

        .btn-sm { display: inline-flex; align-items: center; gap: 4px; padding: 5px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.15s; text-decoration: none; }
        .btn-remove { background: rgba(239,68,68,0.15); color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
        .btn-remove:hover { background: rgba(239,68,68,0.25); }
        .btn-restore { background: rgba(5,150,105,0.15); color: #6ee7b7; border: 1px solid rgba(5,150,105,0.25); }
        .btn-restore:hover { background: rgba(5,150,105,0.25); }
        .btn-view { background: rgba(255,255,255,0.06); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); }
        .btn-view:hover { background: rgba(255,255,255,0.1); color: #e2e8f0; }

        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 50; align-items: center; justify-content: center; }
        .modal-overlay.open { display: flex; }
        .modal { background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 32px; width: 100%; max-width: 440px; }
        .modal-title { font-size: 1.1rem; font-weight: 700; color: white; margin-bottom: 8px; }
        .modal-sub { font-size: 0.875rem; color: #94a3b8; margin-bottom: 20px; }
        .modal-input { width: 100%; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #e2e8f0; border-radius: 8px; padding: 10px 12px; font-size: 0.875rem; font-family: inherit; resize: vertical; }
        .modal-input:focus { outline: none; border-color: rgba(239,68,68,0.5); }
        .modal-actions { display: flex; gap: 10px; margin-top: 16px; justify-content: flex-end; }
        .btn-cancel { background: rgba(255,255,255,0.06); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); }
        .btn-cancel:hover { background: rgba(255,255,255,0.1); }

        .flash-success { background: rgba(5,150,105,0.15); border: 1px solid rgba(5,150,105,0.3); color: #6ee7b7; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem; }
        .empty-state { text-align: center; padding: 64px; color: #475569; }
    </style>
</head>
<body>
<div class="admin-layout">
    {{-- Sidebar --}}
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-name">🩸 BloodLinkBD</div>
            <div class="sidebar-brand-sub">Admin Panel</div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Overview</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item" id="nav-dashboard"><span class="icon">📊</span> Dashboard</a>
            <div class="nav-section-label">Donors</div>
            <a href="{{ route('admin.donors.pending') }}" class="nav-item" id="nav-pending"><span class="icon">👤</span> Pending Verification</a>
            <div class="nav-section-label">Requests</div>
            <a href="{{ route('admin.requests.index') }}" class="nav-item active" id="nav-requests"><span class="icon">🩸</span> Manage Requests</a>
            <div class="nav-section-label">Directory</div>
            <a href="{{ route('admin.hospitals.index') }}" class="nav-item" id="nav-hospitals"><span class="icon">🏥</span> Hospitals</a>
            <div class="nav-section-label">Logs</div>
            <a href="{{ route('admin.audit-log') }}" class="nav-item" id="nav-audit"><span class="icon">📋</span> Audit Log</a>
            <div class="nav-section-label">Public</div>
            <a href="{{ url('/') }}" class="nav-item"><span class="icon">🏠</span> View Site</a>
        </nav>
        <div class="sidebar-footer">
            Logged in as <strong style="color:#94a3b8;">{{ auth()->user()->name }}</strong>
            <form method="POST" action="{{ route('logout') }}" style="margin-top:6px;">
                @csrf <button type="submit" class="btn-sm btn-cancel" style="width:100%;justify-content:center;padding:7px;">Logout</button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">🩸 Manage Blood Requests</div>
            <div style="font-size:0.875rem;color:#64748b;">{{ $requests->total() }} total</div>
        </div>

        <div class="page-body">
            @if (session('success'))
                <div class="flash-success">✅ {{ session('success') }}</div>
            @endif

            {{-- Filter bar --}}
            <form method="GET" action="{{ route('admin.requests.index') }}" class="filter-row" id="filter-requests-form">
                <select name="status" class="filter-select" id="filter-status">
                    <option value="">All Statuses</option>
                    <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
                    <option value="expired"   {{ request('status') === 'expired'   ? 'selected' : '' }}>Expired</option>
                    <option value="removed"   {{ request('status') === 'removed'   ? 'selected' : '' }}>Removed</option>
                    <option value="fulfilled" {{ request('status') === 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                </select>
                <button type="submit" class="filter-btn filter-btn-primary" id="btn-filter-apply">Filter</button>
                <a href="{{ route('admin.requests.index') }}" class="filter-btn filter-btn-ghost" id="btn-filter-clear">Clear</a>
            </form>

            @if ($requests->isEmpty())
                <div class="empty-state">
                    <div style="font-size:3rem;margin-bottom:12px;">🩸</div>
                    <p style="font-size:1rem;font-weight:600;color:#94a3b8;">No requests found.</p>
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table class="request-table">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Patient</th>
                                <th>Blood</th>
                                <th>Urgency</th>
                                <th>District</th>
                                <th>Status</th>
                                <th>Posted</th>
                                <th>Expires</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $req)
                                <tr>
                                    <td style="color:#64748b;font-family:monospace;">#{{ $req->id }}</td>
                                    <td>
                                        <div style="font-weight:600;color:white;">{{ $req->patient_name }}</div>
                                        <div style="font-size:0.75rem;color:#64748b;">{{ $req->hospital }}</div>
                                    </td>
                                    <td>
                                        <span style="font-weight:800;color:#fca5a5;background:rgba(220,38,38,0.2);border:1px solid rgba(220,38,38,0.3);border-radius:6px;padding:3px 8px;font-size:0.8rem;">{{ $req->blood_group }}</span>
                                    </td>
                                    <td>
                                        <span class="urgency-badge urgency-{{ $req->urgency }}">{{ $req->urgency }}</span>
                                    </td>
                                    <td style="color:#94a3b8;">{{ $req->district }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $req->status }}">{{ $req->status }}</span>
                                    </td>
                                    <td style="color:#64748b;font-size:0.8rem;white-space:nowrap;">
                                        {{ $req->created_at->format('d M Y') }}
                                    </td>
                                    <td style="color:#64748b;font-size:0.8rem;white-space:nowrap;">
                                        @if ($req->expires_at)
                                            {{ $req->expires_at->format('d M Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:6px;align-items:center;">
                                            <a href="{{ route('blood-requests.show', $req) }}" class="btn-sm btn-view" target="_blank">View</a>
                                            @if ($req->status !== 'removed')
                                                <button type="button" class="btn-sm btn-remove"
                                                    id="btn-remove-{{ $req->id }}"
                                                    onclick="openRemoveModal({{ $req->id }}, '{{ addslashes($req->blood_group) }} for {{ addslashes($req->patient_name) }}')">
                                                    ✗ Remove
                                                </button>
                                            @else
                                                <form method="POST" action="{{ route('admin.requests.restore', $req) }}">
                                                    @csrf
                                                    <button type="submit" class="btn-sm btn-restore" id="btn-restore-{{ $req->id }}">↩ Restore</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($requests->hasPages())
                    <div style="display:flex;justify-content:center;margin-top:24px;">
                        {{ $requests->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Remove Modal --}}
<div class="modal-overlay" id="remove-modal">
    <div class="modal">
        <div class="modal-title">⚠️ Remove Request</div>
        <div class="modal-sub" id="remove-modal-sub">This will mark the request as removed and hide it from the live feed.</div>
        <form method="POST" id="remove-form">
            @csrf
            <label style="font-size:0.8rem;color:#94a3b8;display:block;margin-bottom:6px;">Reason (optional)</label>
            <textarea name="notes" class="modal-input" rows="3" placeholder="e.g. Duplicate request, phone not reachable, user reported spam..."></textarea>
            <div class="modal-actions">
                <button type="button" class="btn-sm btn-cancel" onclick="closeRemoveModal()">Cancel</button>
                <button type="submit" class="btn-sm btn-remove">Confirm Remove</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRemoveModal(reqId, label) {
    document.getElementById('remove-form').action = '/admin/requests/' + reqId + '/remove';
    document.getElementById('remove-modal-sub').textContent = 'Removing request: ' + label + '. It will be hidden from the live feed.';
    document.getElementById('remove-modal').classList.add('open');
}
function closeRemoveModal() {
    document.getElementById('remove-modal').classList.remove('open');
}
</script>
</body>
</html>
