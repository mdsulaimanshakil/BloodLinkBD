<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Dashboard — {{ config('app.name', 'BloodLinkBD') }}</title>
    <meta name="description" content="BloodLinkBD admin control panel — donor verification, request moderation, and platform statistics.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Figtree', sans-serif; background: #0a0f1e; color: #e2e8f0; min-height: 100vh; }

        /* ── Sidebar Layout ───────────────────────────────── */
        .admin-layout { display: flex; min-height: 100vh; }

        .sidebar {
            width: 240px; flex-shrink: 0;
            background: rgba(255,255,255,0.03);
            border-right: 1px solid rgba(255,255,255,0.07);
            display: flex; flex-direction: column;
            position: sticky; top: 0; height: 100vh;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .sidebar-brand-name {
            font-size: 1.1rem; font-weight: 800;
            background: linear-gradient(135deg, #dc2626, #f59e0b);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .sidebar-brand-sub { font-size: 0.7rem; color: #64748b; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.08em; }

        .sidebar-nav { flex: 1; padding: 16px 12px; }
        .nav-section-label {
            font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.1em; color: #475569; padding: 12px 8px 4px;
        }

        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 8px;
            color: #94a3b8; text-decoration: none; font-size: 0.875rem;
            transition: all 0.15s; margin-bottom: 2px;
        }
        .nav-item:hover { background: rgba(255,255,255,0.06); color: #e2e8f0; }
        .nav-item.active { background: rgba(220,38,38,0.15); color: #fca5a5; border: 1px solid rgba(220,38,38,0.2); }
        .nav-item .icon { font-size: 1rem; width: 20px; text-align: center; }

        .sidebar-footer {
            padding: 16px; border-top: 1px solid rgba(255,255,255,0.07);
            font-size: 0.75rem; color: #475569;
        }

        /* ── Main Content ─────────────────────────────────── */
        .main-content { flex: 1; overflow-x: hidden; }

        .topbar {
            background: rgba(255,255,255,0.02);
            border-bottom: 1px solid rgba(255,255,255,0.07);
            padding: 16px 32px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .topbar-title { font-size: 1.25rem; font-weight: 700; color: white; }
        .topbar-user { font-size: 0.875rem; color: #94a3b8; }

        .page-body { padding: 32px; max-width: 1200px; }

        /* ── Stat Cards ───────────────────────────────────── */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px; }

        .stat-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px; padding: 24px;
            transition: all 0.2s;
        }
        .stat-card:hover { background: rgba(255,255,255,0.06); transform: translateY(-2px); }
        .stat-icon { font-size: 1.5rem; margin-bottom: 12px; }
        .stat-value { font-size: 2.25rem; font-weight: 800; line-height: 1; }
        .stat-label { font-size: 0.75rem; color: #64748b; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.06em; }

        .stat-red    { border-color: rgba(220,38,38,0.25); }
        .stat-red .stat-value { color: #fca5a5; }
        .stat-amber  { border-color: rgba(245,158,11,0.25); }
        .stat-amber .stat-value { color: #fcd34d; }
        .stat-indigo { border-color: rgba(99,102,241,0.25); }
        .stat-indigo .stat-value { color: #a5b4fc; }
        .stat-green  { border-color: rgba(5,150,105,0.25); }
        .stat-green .stat-value { color: #6ee7b7; }
        .stat-blue   { border-color: rgba(59,130,246,0.25); }
        .stat-blue .stat-value { color: #93c5fd; }

        /* ── Sections ─────────────────────────────────────── */
        .section-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 16px;
        }
        .section-title { font-size: 1rem; font-weight: 700; color: white; }

        .card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 12px;
        }

        /* ── Audit Log Table ──────────────────────────────── */
        .audit-table { width: 100%; border-collapse: collapse; }
        .audit-table th {
            padding: 12px 16px; text-align: left;
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: #64748b;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .audit-table td {
            padding: 12px 16px; font-size: 0.875rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            vertical-align: middle;
        }
        .audit-table tr:last-child td { border-bottom: none; }
        .audit-table tr:hover td { background: rgba(255,255,255,0.02); }

        /* ── Action Badges ────────────────────────────────── */
        .action-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 100px;
            font-size: 0.7rem; font-weight: 700; white-space: nowrap;
        }
        .action-verify  { background: rgba(5,150,105,0.2);  color: #6ee7b7;  border: 1px solid rgba(5,150,105,0.3); }
        .action-reject  { background: rgba(239,68,68,0.2);  color: #fca5a5;  border: 1px solid rgba(239,68,68,0.3); }
        .action-remove  { background: rgba(245,158,11,0.2); color: #fcd34d;  border: 1px solid rgba(245,158,11,0.3); }
        .action-restore { background: rgba(99,102,241,0.2); color: #a5b4fc;  border: 1px solid rgba(99,102,241,0.3); }
        .action-default { background: rgba(100,116,139,0.2);color: #94a3b8;  border: 1px solid rgba(100,116,139,0.3); }

        /* ── Buttons ──────────────────────────────────────── */
        .btn-sm {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 5px 12px; border-radius: 6px;
            font-size: 0.75rem; font-weight: 600;
            text-decoration: none; border: none; cursor: pointer;
            transition: all 0.15s;
        }
        .btn-primary { background: linear-gradient(135deg,#dc2626,#b91c1c); color: white; box-shadow: 0 2px 8px rgba(220,38,38,0.3); }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,38,38,0.4); }
        .btn-ghost { background: rgba(255,255,255,0.06); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); }
        .btn-ghost:hover { background: rgba(255,255,255,0.1); color: #e2e8f0; }

        .flash-success { background: rgba(5,150,105,0.15); border: 1px solid rgba(5,150,105,0.3); color: #6ee7b7; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem; }
        .flash-error   { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem; }

        .empty-state { text-align: center; padding: 48px; color: #475569; }
    </style>
</head>
<body>
<div class="admin-layout">

    {{-- ── Sidebar ─────────────────────────────────────────── --}}
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-name">🩸 BloodLinkBD</div>
            <div class="sidebar-brand-sub">Admin Panel</div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Overview</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item active" id="nav-dashboard">
                <span class="icon">📊</span> Dashboard
            </a>

            <div class="nav-section-label">Donors</div>
            <a href="{{ route('admin.donors.pending') }}" class="nav-item" id="nav-donors-pending">
                <span class="icon">👤</span> Pending Verification
                @if ($pendingVerification > 0)
                    <span style="margin-left:auto;background:rgba(220,38,38,0.3);color:#fca5a5;border-radius:100px;padding:1px 7px;font-size:0.7rem;font-weight:700;">{{ $pendingVerification }}</span>
                @endif
            </a>

            <div class="nav-section-label">Requests</div>
            <a href="{{ route('admin.requests.index') }}" class="nav-item" id="nav-requests">
                <span class="icon">🩸</span> Manage Requests
            </a>

            <div class="nav-section-label">Directory</div>
            <a href="{{ route('admin.hospitals.index') }}" class="nav-item" id="nav-hospitals">
                <span class="icon">🏥</span> Hospitals
            </a>

            <div class="nav-section-label">Logs</div>
            <a href="{{ route('admin.audit-log') }}" class="nav-item" id="nav-audit">
                <span class="icon">📋</span> Audit Log
            </a>

            <div class="nav-section-label">Public</div>
            <a href="{{ url('/') }}" class="nav-item" id="nav-home">
                <span class="icon">🏠</span> View Site
            </a>
        </nav>

        <div class="sidebar-footer">
            Logged in as <strong style="color:#94a3b8;">{{ auth()->user()->name }}</strong>
            <form method="POST" action="{{ route('logout') }}" style="margin-top:6px;">
                @csrf
                <button type="submit" class="btn-sm btn-ghost" style="width:100%;justify-content:center;">Logout</button>
            </form>
        </div>
    </aside>

    {{-- ── Main Content ─────────────────────────────────────── --}}
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">📊 Dashboard Overview</div>
            <div class="topbar-user">{{ now()->format('d M Y, H:i') }}</div>
        </div>

        <div class="page-body">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="flash-success">✅ {{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="flash-error">⚠️ {{ session('error') }}</div>
            @endif

            {{-- ── Stats Grid ──────────────────────────────── --}}
            <div class="stats-grid">
                <div class="stat-card stat-red">
                    <div class="stat-icon">🩸</div>
                    <div class="stat-value">{{ number_format($totalDonors) }}</div>
                    <div class="stat-label">Total Donors</div>
                </div>
                <div class="stat-card stat-green">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value">{{ number_format($verifiedDonors) }}</div>
                    <div class="stat-label">Verified Donors</div>
                </div>
                <div class="stat-card stat-amber">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-value">{{ number_format($pendingVerification) }}</div>
                    <div class="stat-label">Pending Verification</div>
                </div>
                <div class="stat-card stat-blue">
                    <div class="stat-icon">💉</div>
                    <div class="stat-value">{{ number_format($availableDonors) }}</div>
                    <div class="stat-label">Available Now</div>
                </div>
                <div class="stat-card stat-indigo">
                    <div class="stat-icon">📋</div>
                    <div class="stat-value">{{ number_format($totalRequests) }}</div>
                    <div class="stat-label">Total Requests</div>
                </div>
                <div class="stat-card stat-green">
                    <div class="stat-icon">🔴</div>
                    <div class="stat-value">{{ number_format($activeRequests) }}</div>
                    <div class="stat-label">Active Requests</div>
                </div>
                <div class="stat-card stat-amber">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-value">{{ number_format($expiredRequests) }}</div>
                    <div class="stat-label">Expired Requests</div>
                </div>
                <div class="stat-card stat-green">
                    <div class="stat-icon">❤️</div>
                    <div class="stat-value">{{ number_format($donationsThisMonth) }}</div>
                    <div class="stat-label">Donations This Month</div>
                </div>
            </div>

            {{-- ── Quick Action Links ───────────────────────── --}}
            <div class="section-header" style="margin-bottom:12px;">
                <div class="section-title">⚡ Quick Actions</div>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:32px;">
                <a href="{{ route('admin.donors.pending') }}" class="btn-sm btn-primary" id="btn-verify-donors">
                    👤 Verify Donors @if($pendingVerification > 0)({{ $pendingVerification }} pending)@endif
                </a>
                <a href="{{ route('admin.requests.index') }}" class="btn-sm btn-ghost" id="btn-manage-requests">
                    🩸 Manage Requests
                </a>
                <a href="{{ route('admin.audit-log') }}" class="btn-sm btn-ghost" id="btn-audit-log">
                    📋 Audit Log
                </a>
                <a href="{{ route('admin.hospitals.index') }}" class="btn-sm btn-ghost" id="btn-hospitals">
                    🏥 Hospitals
                </a>
            </div>

            {{-- ── Recent Audit Activity ────────────────────── --}}
            <div class="section-header">
                <div class="section-title">📋 Recent Admin Activity</div>
                <a href="{{ route('admin.audit-log') }}" class="btn-sm btn-ghost">View All →</a>
            </div>

            <div class="card">
                @if ($recentAuditLogs->isEmpty())
                    <div class="empty-state">
                        <div style="font-size:2.5rem;margin-bottom:12px;">📋</div>
                        <p>No admin actions recorded yet.</p>
                    </div>
                @else
                    <table class="audit-table">
                        <thead>
                            <tr>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Target</th>
                                <th>Notes</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentAuditLogs as $log)
                                <tr>
                                    <td style="color:#e2e8f0;font-weight:600;">{{ $log->admin?->name ?? '—' }}</td>
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
                                    <td style="color:#94a3b8;font-size:0.8rem;">
                                        {{ $log->target_label }} #{{ $log->target_id }}
                                    </td>
                                    <td style="color:#64748b;font-size:0.8rem;max-width:280px;">
                                        {{ Str::limit($log->notes, 60) }}
                                    </td>
                                    <td style="color:#64748b;font-size:0.8rem;white-space:nowrap;">
                                        {{ $log->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

        </div>{{-- /page-body --}}
    </div>{{-- /main-content --}}
</div>{{-- /admin-layout --}}
</body>
</html>
