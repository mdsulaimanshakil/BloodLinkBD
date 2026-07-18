<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pending Donor Verification — Admin | {{ config('app.name') }}</title>
    <meta name="description" content="Review and approve or reject donor verification requests.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Figtree', sans-serif; background: #0a0f1e; color: #e2e8f0; min-height: 100vh; }
        .admin-layout { display: flex; min-height: 100vh; }

        .sidebar {
            width: 240px; flex-shrink: 0;
            background: rgba(255,255,255,0.03);
            border-right: 1px solid rgba(255,255,255,0.07);
            display: flex; flex-direction: column;
            position: sticky; top: 0; height: 100vh; overflow-y: auto;
        }
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
        .topbar { background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.07); padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; }
        .topbar-title { font-size: 1.25rem; font-weight: 700; color: white; }
        .page-body { padding: 32px; }

        .card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; }
        .donor-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; padding: 20px; transition: all 0.2s; }
        .donor-card:hover { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.12); }

        .avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg,rgba(220,38,38,0.3),rgba(185,28,28,0.2)); border: 2px solid rgba(220,38,38,0.4); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 800; color: #fca5a5; flex-shrink: 0; }

        .blood-pill { display: inline-flex; align-items: center; justify-content: center; padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 800; color: #fca5a5; background: rgba(220,38,38,0.2); border: 1px solid rgba(220,38,38,0.3); }
        .info-pill { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 100px; font-size: 0.75rem; color: #94a3b8; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); }

        .btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.15s; text-decoration: none; }
        .btn-approve { background: linear-gradient(135deg,#059669,#047857); color: white; box-shadow: 0 2px 8px rgba(5,150,105,0.3); }
        .btn-approve:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(5,150,105,0.4); }
        .btn-reject { background: rgba(239,68,68,0.15); color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
        .btn-reject:hover { background: rgba(239,68,68,0.25); }

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

        .pagination-links a, .pagination-links span { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; font-size: 0.8rem; margin: 0 2px; text-decoration: none; transition: all 0.15s; }
        .pagination-links a { color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); }
        .pagination-links a:hover { background: rgba(255,255,255,0.08); color: #e2e8f0; }
        .pagination-links span[aria-current] { background: rgba(220,38,38,0.3); color: #fca5a5; border: 1px solid rgba(220,38,38,0.4); }
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
            <a href="{{ route('admin.donors.pending') }}" class="nav-item active" id="nav-pending"><span class="icon">👤</span> Pending Verification</a>
            <div class="nav-section-label">Requests</div>
            <a href="{{ route('admin.requests.index') }}" class="nav-item" id="nav-requests"><span class="icon">🩸</span> Manage Requests</a>
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
                @csrf <button type="submit" class="btn btn-cancel" style="width:100%;justify-content:center;">Logout</button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">👤 Pending Donor Verification</div>
            <div style="font-size:0.875rem;color:#64748b;">{{ $donors->total() }} pending</div>
        </div>

        <div class="page-body">
            @if (session('success'))
                <div class="flash-success">✅ {{ session('success') }}</div>
            @endif

            @if ($donors->isEmpty())
                <div class="empty-state">
                    <div style="font-size:3rem;margin-bottom:12px;">🎉</div>
                    <p style="font-size:1.1rem;font-weight:600;color:#94a3b8;">All donors verified!</p>
                    <p style="font-size:0.875rem;margin-top:4px;">No pending verification requests at the moment.</p>
                    <a href="{{ route('admin.dashboard') }}" style="display:inline-block;margin-top:20px;padding:9px 18px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#94a3b8;text-decoration:none;font-size:0.875rem;">← Back to Dashboard</a>
                </div>
            @else
                <div style="display:grid;gap:14px;">
                    @foreach ($donors as $donor)
                        <div class="donor-card">
                            <div style="display:flex;align-items:flex-start;gap:16px;flex-wrap:wrap;">
                                <div class="avatar">{{ substr($donor->user?->name ?? '?', 0, 1) }}</div>

                                <div style="flex:1;min-width:0;">
                                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px;">
                                        <span style="font-weight:700;color:white;">{{ $donor->user?->name ?? 'Unknown' }}</span>
                                        <span class="blood-pill">{{ $donor->blood_group }}</span>
                                    </div>
                                    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px;">
                                        <span class="info-pill">📍 {{ $donor->district }}</span>
                                        <span class="info-pill">📞 {{ $donor->phone }}</span>
                                        @if ($donor->last_donation_date)
                                            <span class="info-pill">💉 Last: {{ $donor->last_donation_date->format('d M Y') }}</span>
                                        @else
                                            <span class="info-pill">💉 First-time donor</span>
                                        @endif
                                        <span class="info-pill">📧 {{ $donor->user?->email }}</span>
                                        <span class="info-pill">🕐 Joined {{ $donor->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div style="display:flex;gap:8px;align-items:flex-start;flex-shrink:0;">
                                    {{-- Approve --}}
                                    <form method="POST" action="{{ route('admin.donors.verify', $donor) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-approve" id="btn-verify-{{ $donor->id }}">✅ Approve</button>
                                    </form>

                                    {{-- Reject (opens modal) --}}
                                    <button type="button"
                                        class="btn btn-reject"
                                        id="btn-reject-{{ $donor->id }}"
                                        onclick="openRejectModal({{ $donor->id }}, '{{ addslashes($donor->user?->name) }}')">
                                        ✗ Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if ($donors->hasPages())
                    <div class="pagination-links" style="display:flex;justify-content:center;margin-top:24px;">
                        {{ $donors->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal-overlay" id="reject-modal">
    <div class="modal">
        <div class="modal-title">✗ Reject Donor</div>
        <div class="modal-sub" id="reject-modal-sub">Rejecting donor — their profile will be marked as not verified.</div>
        <form method="POST" id="reject-form">
            @csrf
            <label style="font-size:0.8rem;color:#94a3b8;display:block;margin-bottom:6px;">Reason (optional)</label>
            <textarea name="notes" class="modal-input" rows="3" placeholder="e.g. Phone number appears invalid, duplicate entry..."></textarea>
            <div class="modal-actions">
                <button type="button" class="btn btn-cancel" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-reject">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(donorId, donorName) {
    document.getElementById('reject-form').action = '/admin/donors/' + donorId + '/reject';
    document.getElementById('reject-modal-sub').textContent = 'Rejecting profile for ' + donorName + '. Their verification will be marked invalid.';
    document.getElementById('reject-modal').classList.add('open');
}
function closeRejectModal() {
    document.getElementById('reject-modal').classList.remove('open');
}
</script>
</body>
</html>
