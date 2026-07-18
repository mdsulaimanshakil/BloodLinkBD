<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Audit Log — {{ config('app.name') }}</title>
    <meta name="description" content="Full admin activity audit trail — every admin action is recorded here.">
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
        .filter-select { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #e2e8f0; border-radius: 8px; padding: 8px 12px; font-size: 0.875rem; font-family: inherit; }
        .filter-select option { background: #1e293b; }
        .filter-btn { padding: 8px 16px; border-radius: 8px; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.15s; }
        .filter-btn-primary { background: linear-gradient(135deg,#dc2626,#b91c1c); color: white; }
        .filter-btn-ghost { background: rgba(255,255,255,0.06); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); text-decoration: none; display: inline-flex; align-items: center; }

        .audit-table { width: 100%; border-collapse: collapse; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; overflow: hidden; }
        .audit-table th { padding: 13px 18px; text-align: left; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; border-bottom: 1px solid rgba(255,255,255,0.07); }
        .audit-table td { padding: 13px 18px; font-size: 0.875rem; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
        .audit-table tr:last-child td { border-bottom: none; }
        .audit-table tr:hover td { background: rgba(255,255,255,0.02); }

        .action-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; border-radius: 100px; font-size: 0.7rem; font-weight: 700; white-space: nowrap; }
        .action-verify  { background: rgba(5,150,105,0.2);  color: #6ee7b7;  border: 1px solid rgba(5,150,105,0.3); }
        .action-reject  { background: rgba(239,68,68,0.2);  color: #fca5a5;  border: 1px solid rgba(239,68,68,0.3); }
        .action-remove  { background: rgba(245,158,11,0.2); color: #fcd34d;  border: 1px solid rgba(245,158,11,0.3); }
        .action-restore { background: rgba(99,102,241,0.2); color: #a5b4fc;  border: 1px solid rgba(99,102,241,0.3); }
        .action-default { background: rgba(100,116,139,0.2);color: #94a3b8;  border: 1px solid rgba(100,116,139,0.3); }

        .admin-avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg,rgba(99,102,241,0.3),rgba(79,70,229,0.2)); border: 1px solid rgba(99,102,241,0.4); display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800; color: #a5b4fc; flex-shrink: 0; }

        .empty-state { text-align: center; padding: 64px; color: #475569; }
        .btn-sm { display: inline-flex; align-items: center; gap: 4px; padding: 7px 14px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.15s; }
        .btn-cancel { background: rgba(255,255,255,0.06); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); }
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
            <a href="{{ route('admin.requests.index') }}" class="nav-item" id="nav-requests"><span class="icon">🩸</span> Manage Requests</a>
            <div class="nav-section-label">Directory</div>
            <a href="{{ route('admin.hospitals.index') }}" class="nav-item" id="nav-hospitals"><span class="icon">🏥</span> Hospitals</a>
            <div class="nav-section-label">Logs</div>
            <a href="{{ route('admin.audit-log') }}" class="nav-item active" id="nav-audit"><span class="icon">📋</span> Audit Log</a>
            <div class="nav-section-label">Public</div>
            <a href="{{ url('/') }}" class="nav-item"><span class="icon">🏠</span> View Site</a>
        </nav>
        <div class="sidebar-footer">
            Logged in as <strong style="color:#94a3b8;">{{ auth()->user()->name }}</strong>
            <form method="POST" action="{{ route('logout') }}" style="margin-top:6px;">
                @csrf <button type="submit" class="btn-sm btn-cancel" style="width:100%;justify-content:center;">Logout</button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">📋 Admin Audit Log</div>
            <div style="font-size:0.875rem;color:#64748b;">{{ $logs->total() }} entries</div>
        </div>

        <div class="page-body">

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.audit-log') }}" class="filter-row" id="audit-filter-form">
                <select name="action" class="filter-select" id="filter-action">
                    <option value="">All Actions</option>
                    <option value="verify_donor"    {{ request('action') === 'verify_donor'    ? 'selected' : '' }}>Verify Donor</option>
                    <option value="reject_donor"    {{ request('action') === 'reject_donor'    ? 'selected' : '' }}>Reject Donor</option>
                    <option value="remove_request"  {{ request('action') === 'remove_request'  ? 'selected' : '' }}>Remove Request</option>
                    <option value="restore_request" {{ request('action') === 'restore_request' ? 'selected' : '' }}>Restore Request</option>
                </select>

                <select name="admin_id" class="filter-select" id="filter-admin">
                    <option value="">All Admins</option>
                    @foreach ($admins as $admin)
                        <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>
                            {{ $admin->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="filter-btn filter-btn-primary" id="btn-audit-filter">Filter</button>
                <a href="{{ route('admin.audit-log') }}" class="filter-btn filter-btn-ghost" id="btn-audit-clear">Clear</a>
            </form>

            @if ($logs->isEmpty())
                <div class="empty-state">
                    <div style="font-size:3rem;margin-bottom:12px;">📋</div>
                    <p style="font-size:1rem;font-weight:600;color:#94a3b8;">No audit entries found.</p>
                    <p style="font-size:0.875rem;margin-top:4px;">Admin actions will appear here as they happen.</p>
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table class="audit-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Target</th>
                                <th>Notes</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td style="color:#475569;font-family:monospace;font-size:0.8rem;">{{ $log->id }}</td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div class="admin-avatar">{{ substr($log->admin?->name ?? '?', 0, 1) }}</div>
                                            <div>
                                                <div style="font-weight:600;color:#e2e8f0;font-size:0.875rem;">{{ $log->admin?->name ?? 'Unknown' }}</div>
                                                <div style="font-size:0.7rem;color:#64748b;">{{ $log->admin?->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match($log->action) {
                                                'verify_donor'    => 'action-verify',
                                                'reject_donor'    => 'action-reject',
                                                'remove_request'  => 'action-remove',
                                                'restore_request' => 'action-restore',
                                                default           => 'action-default',
                                            };
                                        @endphp
                                        <span class="action-badge {{ $badgeClass }}">{{ $log->action_label }}</span>
                                    </td>
                                    <td>
                                        <div style="font-size:0.8rem;color:#94a3b8;">{{ $log->target_label }}</div>
                                        <div style="font-size:0.75rem;color:#475569;font-family:monospace;">#{{ $log->target_id }}</div>
                                    </td>
                                    <td style="max-width:300px;">
                                        @if ($log->notes)
                                            <span style="font-size:0.8rem;color:#94a3b8;" title="{{ $log->notes }}">
                                                {{ Str::limit($log->notes, 80) }}
                                            </span>
                                        @else
                                            <span style="color:#475569;font-size:0.8rem;">—</span>
                                        @endif
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <div style="font-size:0.8rem;color:#94a3b8;">{{ $log->created_at->format('d M Y') }}</div>
                                        <div style="font-size:0.75rem;color:#64748b;">{{ $log->created_at->format('H:i:s') }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($logs->hasPages())
                    <div style="display:flex;justify-content:center;margin-top:24px;">
                        {{ $logs->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
</body>
</html>
