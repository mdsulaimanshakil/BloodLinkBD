<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Platform Statistics — {{ config('app.name', 'BloodLinkBD') }}</title>
    <meta name="description" content="See how BloodLinkBD is saving lives across Bangladesh — total donors, fulfilled blood requests, and monthly impact stats.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Figtree', sans-serif; background: #060b18; color: #e2e8f0; margin: 0; }

        /* ── Hero ─────────────────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, #060b18 0%, #130a1e 50%, #060b18 100%);
            border-bottom: 1px solid rgba(220,38,38,0.15);
            padding: 80px 20px 56px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse 60% 50% at 50% 0%, rgba(220,38,38,0.12), transparent);
            pointer-events: none;
        }
        .hero-eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(220,38,38,0.12); border: 1px solid rgba(220,38,38,0.3);
            color: #fca5a5; border-radius: 100px; padding: 6px 16px;
            font-size: 0.8rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; margin-bottom: 20px;
        }
        .hero-title {
            font-size: clamp(2.25rem, 6vw, 3.75rem);
            font-weight: 900; line-height: 1.1;
            color: white; margin-bottom: 16px;
        }
        .hero-title span {
            background: linear-gradient(135deg, #dc2626, #f59e0b);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero-sub { font-size: 1.1rem; color: #94a3b8; max-width: 520px; margin: 0 auto 32px; line-height: 1.6; }

        .cache-note {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 0.75rem; color: #475569;
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 100px; padding: 4px 12px;
        }

        /* ── Layout ─────────────────────────────────────────── */
        .page { max-width: 1100px; margin: 0 auto; padding: 56px 20px; }

        /* ── Primary Stats Grid ──────────────────────────────── */
        .primary-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 48px; }

        .stat-hero-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px; padding: 32px 28px;
            position: relative; overflow: hidden;
            transition: all 0.25s;
        }
        .stat-hero-card:hover { transform: translateY(-4px); background: rgba(255,255,255,0.06); }

        .stat-hero-card.red   { border-color: rgba(220,38,38,0.3); }
        .stat-hero-card.red::before   { background: radial-gradient(circle at top right, rgba(220,38,38,0.15), transparent 60%); }
        .stat-hero-card.amber { border-color: rgba(245,158,11,0.3); }
        .stat-hero-card.amber::before { background: radial-gradient(circle at top right, rgba(245,158,11,0.12), transparent 60%); }
        .stat-hero-card.indigo{ border-color: rgba(99,102,241,0.3); }
        .stat-hero-card.indigo::before{ background: radial-gradient(circle at top right, rgba(99,102,241,0.12), transparent 60%); }
        .stat-hero-card.green { border-color: rgba(5,150,105,0.3); }
        .stat-hero-card.green::before { background: radial-gradient(circle at top right, rgba(5,150,105,0.12), transparent 60%); }

        .stat-hero-card::before {
            content: ''; position: absolute; inset: 0; pointer-events: none;
        }

        .stat-icon { font-size: 2rem; margin-bottom: 16px; }
        .stat-value {
            font-size: 3.5rem; font-weight: 900; line-height: 1;
            margin-bottom: 6px;
            counter-increment: none;
        }
        .stat-hero-card.red   .stat-value { color: #fca5a5; }
        .stat-hero-card.amber .stat-value { color: #fcd34d; }
        .stat-hero-card.indigo .stat-value { color: #a5b4fc; }
        .stat-hero-card.green  .stat-value { color: #6ee7b7; }
        .stat-label { font-size: 0.875rem; color: #94a3b8; font-weight: 500; }
        .stat-sub   { font-size: 0.75rem; color: #475569; margin-top: 6px; }

        /* ── Section Title ─────────────────────────────────── */
        .section-title {
            font-size: 1.25rem; font-weight: 800; color: white;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 8px;
        }
        .section-title::after {
            content: ''; flex: 1; height: 1px;
            background: linear-gradient(90deg, rgba(255,255,255,0.08), transparent);
        }

        /* ── Blood Group Grid ─────────────────────────────── */
        .blood-group-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px;
            margin-bottom: 48px;
        }
        .blood-group-card {
            background: rgba(220,38,38,0.06); border: 1px solid rgba(220,38,38,0.2);
            border-radius: 14px; padding: 18px 12px; text-align: center;
            transition: all 0.2s;
        }
        .blood-group-card:hover { background: rgba(220,38,38,0.12); transform: translateY(-2px); }
        .blood-group-type { font-size: 1.6rem; font-weight: 900; color: #fca5a5; }
        .blood-group-count { font-size: 1.1rem; font-weight: 700; color: white; margin-top: 4px; }
        .blood-group-label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 2px; }

        /* ── Two Column ───────────────────────────────────── */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 48px; }
        @media (max-width: 640px) { .two-col { grid-template-columns: 1fr; } }

        .card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px; padding: 24px;
        }
        .card-title { font-size: 0.875rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 16px; }

        /* ── Top Districts ─────────────────────────────────── */
        .district-row {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .district-row:last-child { border-bottom: none; }
        .district-rank { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 800; flex-shrink: 0; }
        .rank-1 { background: rgba(245,158,11,0.25); color: #fcd34d; }
        .rank-2 { background: rgba(100,116,139,0.25); color: #cbd5e1; }
        .rank-3 { background: rgba(180,83,9,0.2); color: #fdba74; }
        .rank-other { background: rgba(255,255,255,0.06); color: #64748b; }
        .district-name { flex: 1; font-weight: 600; color: #e2e8f0; font-size: 0.9rem; }
        .district-bar-wrap { width: 100px; }
        .district-bar { height: 6px; border-radius: 100px; background: rgba(220,38,38,0.15); overflow: hidden; }
        .district-bar-fill { height: 100%; border-radius: 100px; background: linear-gradient(90deg, #dc2626, #f59e0b); transition: width 0.5s ease; }
        .district-count { font-size: 0.8rem; font-weight: 700; color: #fca5a5; min-width: 28px; text-align: right; }

        /* ── Monthly Trend Bar Chart ──────────────────────── */
        .trend-chart { display: flex; align-items: flex-end; gap: 8px; height: 120px; padding-top: 8px; }
        .trend-bar-wrap { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; height: 100%; justify-content: flex-end; }
        .trend-bar {
            width: 100%; border-radius: 6px 6px 0 0;
            background: linear-gradient(180deg, rgba(220,38,38,0.7), rgba(220,38,38,0.3));
            border: 1px solid rgba(220,38,38,0.4);
            min-height: 4px;
            transition: height 0.4s ease;
        }
        .trend-bar.current { background: linear-gradient(180deg, #dc2626, rgba(220,38,38,0.6)); }
        .trend-label { font-size: 0.65rem; color: #64748b; text-align: center; white-space: nowrap; }
        .trend-count  { font-size: 0.75rem; font-weight: 700; color: #fca5a5; }

        /* ── Secondary Stats Row ──────────────────────────── */
        .secondary-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; margin-bottom: 48px; }
        .mini-stat {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);
            border-radius: 12px; padding: 18px 16px; text-align: center;
        }
        .mini-stat-value { font-size: 1.75rem; font-weight: 800; color: white; }
        .mini-stat-label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 4px; }

        /* ── Nav ──────────────────────────────────────────── */
        .topnav {
            background: rgba(6,11,24,0.9); backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding: 16px 24px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 20;
        }
        .brand { font-size: 1.1rem; font-weight: 800; background: linear-gradient(135deg,#dc2626,#f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-decoration: none; }
        .nav-links { display: flex; gap: 20px; }
        .nav-link { color: #94a3b8; text-decoration: none; font-size: 0.875rem; transition: color 0.2s; }
        .nav-link:hover { color: #e2e8f0; }
        .nav-link.active { color: #fca5a5; }

        /* ── CTA ──────────────────────────────────────────── */
        .cta-section {
            background: linear-gradient(135deg, rgba(220,38,38,0.1), rgba(185,28,28,0.05));
            border: 1px solid rgba(220,38,38,0.2); border-radius: 20px;
            padding: 48px; text-align: center; margin-top: 16px;
        }
        .cta-title { font-size: 1.75rem; font-weight: 800; color: white; margin-bottom: 10px; }
        .cta-sub { font-size: 1rem; color: #94a3b8; margin-bottom: 28px; }
        .btn-cta {
            display: inline-flex; align-items: center; gap: 8px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white; border: none; border-radius: 10px;
            padding: 14px 28px; font-weight: 700; font-size: 1rem;
            text-decoration: none; cursor: pointer;
            box-shadow: 0 6px 20px rgba(220,38,38,0.4);
            transition: all 0.2s;
        }
        .btn-cta:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(220,38,38,0.5); }
        .btn-ghost-sm {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.06); color: #94a3b8;
            border: 1px solid rgba(255,255,255,0.12); border-radius: 10px;
            padding: 14px 24px; font-size: 0.9rem; text-decoration: none;
            transition: all 0.2s; margin-left: 12px;
        }
        .btn-ghost-sm:hover { background: rgba(255,255,255,0.1); color: #e2e8f0; }
    </style>
</head>
<body>

{{-- Navigation --}}
<nav class="topnav">
    <a href="{{ url('/') }}" class="brand">🩸 BloodLinkBD</a>
    <div class="nav-links">
        <a href="{{ route('blood-requests.create') }}" class="nav-link">Request Blood</a>
        <a href="{{ route('donor-search') }}" class="nav-link">Find Donors</a>
        <a href="{{ route('live-feed') }}" class="nav-link">Live Feed</a>
        <a href="{{ route('hospitals.index') }}" class="nav-link">Hospitals</a>
        <a href="{{ route('stats') }}" class="nav-link active">Stats</a>
    </div>
</nav>

{{-- Hero --}}
<div class="hero">
    <div class="hero-eyebrow">📊 Platform Impact</div>
    <h1 class="hero-title">
        <span>Lives Saved</span> Across<br>Bangladesh
    </h1>
    <p class="hero-sub">
        Real numbers from real people. Every donation recorded here represents a life touched by the BloodLinkBD community.
    </p>
    <div class="cache-note">🔄 Stats refreshed every 10 minutes</div>
</div>

<div class="page">

    {{-- ── Primary Stats ──────────────────────────────────── --}}
    <div class="primary-stats">
        <div class="stat-hero-card red">
            <div class="stat-icon">🩸</div>
            <div class="stat-value" id="count-donors">{{ number_format($totalDonors) }}</div>
            <div class="stat-label">Verified Donors</div>
            <div class="stat-sub">{{ number_format($availableDonors) }} available right now</div>
        </div>

        <div class="stat-hero-card amber">
            <div class="stat-icon">❤️</div>
            <div class="stat-value">{{ number_format($livesThisMonth) }}</div>
            <div class="stat-label">Lives Helped This Month</div>
            <div class="stat-sub">{{ number_format($livesThisYear) }} this year</div>
        </div>

        <div class="stat-hero-card green">
            <div class="stat-icon">✅</div>
            <div class="stat-value">{{ number_format($totalFulfilled) }}</div>
            <div class="stat-label">Requests Fulfilled</div>
            <div class="stat-sub">{{ number_format($totalDonations) }} total donations recorded</div>
        </div>

        <div class="stat-hero-card indigo">
            <div class="stat-icon">🔴</div>
            <div class="stat-value">{{ number_format($activeRequests) }}</div>
            <div class="stat-label">Active Requests Now</div>
            @if ($criticalRequests > 0)
                <div class="stat-sub" style="color:#fca5a5;">⚠️ {{ $criticalRequests }} critical</div>
            @else
                <div class="stat-sub">No critical requests</div>
            @endif
        </div>
    </div>

    {{-- ── Donors by Blood Group ─────────────────────────── --}}
    <div class="section-title">🩸 Donors by Blood Group</div>
    <div class="blood-group-grid">
        @php
            $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        @endphp
        @foreach ($bloodGroups as $bg)
            <div class="blood-group-card">
                <div class="blood-group-type">{{ $bg }}</div>
                <div class="blood-group-count">{{ number_format($byBloodGroup->get($bg, 0)) }}</div>
                <div class="blood-group-label">donors</div>
            </div>
        @endforeach
    </div>

    {{-- ── Two Column: Districts + Trend ──────────────────── --}}
    <div class="two-col">

        {{-- Top Districts --}}
        <div class="card">
            <div class="card-title">📍 Top Districts by Donors</div>
            @php $maxDistrict = $topDistricts->max('total') ?: 1; @endphp
            @forelse ($topDistricts as $i => $district)
                <div class="district-row">
                    <div class="district-rank {{ match($i) { 0 => 'rank-1', 1 => 'rank-2', 2 => 'rank-3', default => 'rank-other' } }}">
                        {{ $i + 1 }}
                    </div>
                    <div class="district-name">{{ $district->district }}</div>
                    <div class="district-bar-wrap">
                        <div class="district-bar">
                            <div class="district-bar-fill" style="width:{{ round(($district->total / $maxDistrict) * 100) }}%;"></div>
                        </div>
                    </div>
                    <div class="district-count">{{ $district->total }}</div>
                </div>
            @empty
                <p style="color:#475569;font-size:0.875rem;">No data yet.</p>
            @endforelse
        </div>

        {{-- Monthly Trend --}}
        <div class="card">
            <div class="card-title">📈 Donation Trend (Last 6 Months)</div>
            @php $maxMonth = max(array_column($monthlyTrend, 'count')) ?: 1; @endphp
            <div class="trend-chart">
                @foreach ($monthlyTrend as $i => $month)
                    @php
                        $heightPct = max(4, round(($month['count'] / $maxMonth) * 100));
                        $isCurrent = $i === count($monthlyTrend) - 1;
                    @endphp
                    <div class="trend-bar-wrap">
                        <div class="trend-count">{{ $month['count'] > 0 ? $month['count'] : '' }}</div>
                        <div class="trend-bar {{ $isCurrent ? 'current' : '' }}"
                             style="height: {{ $heightPct }}%;"></div>
                        <div class="trend-label">{{ $month['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Secondary Stats ─────────────────────────────────── --}}
    <div class="section-title">📊 More Numbers</div>
    <div class="secondary-stats">
        <div class="mini-stat">
            <div class="mini-stat-value" style="color:#6ee7b7;">{{ number_format($totalDonations) }}</div>
            <div class="mini-stat-label">All-Time Donations</div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-value" style="color:#fcd34d;">{{ number_format($livesThisYear) }}</div>
            <div class="mini-stat-label">Lives Helped This Year</div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-value" style="color:#fca5a5;">{{ number_format($availableDonors) }}</div>
            <div class="mini-stat-label">Available Donors Now</div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-value" style="color:#a5b4fc;">{{ number_format($activeRequests) }}</div>
            <div class="mini-stat-label">Active Requests</div>
        </div>
        @if ($criticalRequests > 0)
            <div class="mini-stat" style="border-color:rgba(220,38,38,0.3);">
                <div class="mini-stat-value" style="color:#fca5a5;">{{ number_format($criticalRequests) }}</div>
                <div class="mini-stat-label">Critical Requests</div>
            </div>
        @endif
    </div>

    {{-- ── CTA ─────────────────────────────────────────────── --}}
    <div class="cta-section">
        <div class="cta-title">🩸 Be Part of These Numbers</div>
        <p class="cta-sub">Every registered donor is a potential lifesaver. Join today — it costs nothing.</p>
        <a href="{{ route('blood-requests.create') }}" class="btn-cta" id="cta-request">🚨 Post a Request</a>
        <a href="{{ url('/register') }}" class="btn-ghost-sm" id="cta-register">Register as Donor →</a>
    </div>

</div>{{-- /page --}}

</body>
</html>
