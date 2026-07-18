<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>My Donor Dashboard — {{ config('app.name', 'BloodLinkBD') }}</title>
    <meta name="description" content="View your donation history, trust score, and Trusted Donor badge on BloodLinkBD.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Figtree', sans-serif; background: #0f172a; color: #e2e8f0; }

        .dashboard-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #0f172a 100%);
            border-bottom: 1px solid rgba(220,38,38,0.2);
        }

        .card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 24px;
        }

        /* Stat cards */
        .stat-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.2s;
        }
        .stat-card:hover { background: rgba(255,255,255,0.07); transform: translateY(-2px); }
        .stat-number { font-size: 2.5rem; font-weight: 800; line-height: 1; }
        .stat-label  { font-size: 0.75rem; color: #94a3b8; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.06em; }

        /* Trusted Donor Badge */
        .trusted-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, rgba(217,119,6,0.2), rgba(180,83,9,0.15));
            border: 1.5px solid rgba(217,119,6,0.5);
            color: #fcd34d;
            padding: 8px 20px;
            border-radius: 100px;
            font-size: 0.875rem;
            font-weight: 700;
            animation: glow-badge 3s ease-in-out infinite;
        }

        @keyframes glow-badge {
            0%, 100% { box-shadow: 0 0 0 rgba(217,119,6,0); }
            50%       { box-shadow: 0 0 20px rgba(217,119,6,0.3); }
        }

        .blood-badge-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px; height: 52px;
            background: linear-gradient(135deg, rgba(220,38,38,0.25), rgba(185,28,28,0.15));
            border: 1.5px solid rgba(220,38,38,0.4);
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 800;
            color: #fca5a5;
        }

        .status-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 12px; border-radius: 100px;
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;
        }
        .status-available   { background: rgba(5,150,105,0.2);  color: #6ee7b7; border: 1px solid rgba(5,150,105,0.3); }
        .status-unavailable { background: rgba(100,116,139,0.2); color: #94a3b8; border: 1px solid rgba(100,116,139,0.3); }
        .status-verified    { background: rgba(99,102,241,0.2);  color: #a5b4fc; border: 1px solid rgba(99,102,241,0.3); }

        /* Timeline */
        .timeline-item {
            position: relative;
            padding-left: 28px;
        }
        .timeline-item::before {
            content: '';
            position: absolute; left: 0; top: 6px;
            width: 10px; height: 10px;
            background: #dc2626; border-radius: 50%;
            box-shadow: 0 0 0 3px rgba(220,38,38,0.2);
        }
        .timeline-item::after {
            content: '';
            position: absolute; left: 4px; top: 16px;
            width: 2px; height: calc(100% + 8px);
            background: rgba(220,38,38,0.2);
        }
        .timeline-item:last-child::after { display: none; }

        .rating-stars { color: #fbbf24; font-size: 1rem; letter-spacing: 1px; }

        .avatar-circle {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, rgba(220,38,38,0.3), rgba(185,28,28,0.2));
            border: 2px solid rgba(220,38,38,0.4);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.75rem; font-weight: 800; color: #fca5a5;
        }

        .nav-link {
            color: #94a3b8; text-decoration: none; font-size: 0.875rem;
            transition: color 0.2s;
        }
        .nav-link:hover { color: #e2e8f0; }

        .empty-state { text-align: center; padding: 48px 20px; color: #64748b; }
        .empty-icon  { font-size: 3rem; margin-bottom: 12px; }

        .btn-primary {
            display: inline-flex; align-items: center; gap: 6px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white; border: none; border-radius: 8px;
            padding: 9px 18px; font-weight: 600; font-size: 0.875rem;
            text-decoration: none; cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(220,38,38,0.3);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(220,38,38,0.4); }

        .btn-ghost {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.06); color: #94a3b8;
            border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;
            padding: 9px 16px; font-size: 0.875rem; text-decoration: none;
            transition: all 0.2s;
        }
        .btn-ghost:hover { background: rgba(255,255,255,0.1); color: #e2e8f0; }
    </style>
</head>
<body>

{{-- Navigation --}}
<nav class="dashboard-hero px-4 py-6">
    <div class="max-w-5xl mx-auto flex items-center justify-between">
        <div>
            <a href="{{ url('/') }}" class="nav-link">&larr; Home</a>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('live-feed') }}" class="nav-link">🩸 Live Feed</a>
            <a href="{{ route('donor-search') }}" class="nav-link">🔍 Find Donors</a>
        </div>
    </div>
</nav>

{{-- Hero --}}
<div class="dashboard-hero pb-10 px-4">
    <div class="max-w-5xl mx-auto">
        {{-- Flash --}}
        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-900/30 border border-green-500/30 text-green-300 text-sm">✅ {{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="mb-4 p-3 rounded-lg bg-blue-900/30 border border-blue-500/30 text-blue-300 text-sm">ℹ️ {{ session('info') }}</div>
        @endif

        <div class="flex flex-wrap items-start gap-6">
            {{-- Avatar --}}
            <div class="avatar-circle">{{ substr($user->name, 0, 1) }}</div>

            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-white">{{ $user->name }}</h1>

                    @if ($profile?->is_trusted)
                        <span class="trusted-badge">⭐ Trusted Donor</span>
                    @endif
                </div>
                <p class="text-slate-400 text-sm">{{ $user->email }}</p>

                @if ($profile)
                    <div class="flex flex-wrap gap-2 mt-3">
                        <div class="blood-badge-pill">{{ $profile->blood_group }}</div>
                        <span class="status-badge {{ $profile->is_available ? 'status-available' : 'status-unavailable' }}">
                            {{ $profile->is_available ? '✓ Available' : '✗ Unavailable' }}
                        </span>
                        @if ($profile->is_verified)
                            <span class="status-badge status-verified">✓ Verified</span>
                        @endif
                        <span class="status-badge" style="background:rgba(220,38,38,0.15);color:#fca5a5;border:1px solid rgba(220,38,38,0.3);">
                            📍 {{ $profile->district }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Stats Row --}}
<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="stat-card">
            <div class="stat-number text-red-400">{{ $profile?->donation_count ?? 0 }}</div>
            <div class="stat-label">Total Donations</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-yellow-400">
                {{ $avgRating ? number_format($avgRating, 1) : '—' }}
            </div>
            <div class="stat-label">Avg Rating</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-indigo-400">
                {{ $profile?->trust_score ? number_format($profile->trust_score, 1) : '—' }}
            </div>
            <div class="stat-label">Trust Score</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-emerald-400">
                {{ $profile?->days_since_last_donation !== null ? $profile->days_since_last_donation . 'd' : '—' }}
            </div>
            <div class="stat-label">Days Since Last</div>
        </div>
    </div>

    {{-- Trusted Donor Badge Info --}}
    @if ($profile)
        <div class="card mb-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="font-semibold text-white text-lg">⭐ Trusted Donor Badge</h2>
                    <p class="text-slate-400 text-sm mt-1">
                        @if ($profile->is_trusted)
                            You've earned the Trusted Donor badge! You have {{ $profile->donation_count }} donations.
                        @else
                            Complete {{ 3 - min($profile->donation_count, 3) }} more donation(s) to earn the Trusted Donor badge.
                        @endif
                    </p>
                    <div class="mt-3 w-full max-w-xs">
                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                            <span>{{ $profile->donation_count }} / 3 donations</span>
                            @if ($profile->is_trusted) <span class="text-yellow-400">✓ Achieved!</span> @endif
                        </div>
                        <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500"
                                 style="width: {{ min(100, ($profile->donation_count / 3) * 100) }}%;
                                        background: linear-gradient(90deg, #dc2626, #f59e0b);">
                            </div>
                        </div>
                    </div>
                </div>
                @if ($profile->is_trusted)
                    <span class="trusted-badge text-xl">⭐ Trusted Donor</span>
                @endif
            </div>
        </div>
    @endif

    {{-- Donation History Timeline --}}
    <div class="card">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-semibold text-white text-lg">🕐 Donation History</h2>
            <span class="text-sm text-slate-400">{{ $history->count() }} record(s)</span>
        </div>

        @if ($history->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">🩸</div>
                <p class="font-medium text-slate-300">No donations recorded yet</p>
                <p class="text-sm mt-1">Your donation history will appear here after you donate.</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach ($history as $record)
                    <div class="timeline-item">
                        <div class="card" style="padding:16px;">
                            <div class="flex items-start justify-between gap-3 flex-wrap">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <span class="font-semibold text-white text-sm">
                                            {{ $record->donated_at?->format('d M Y') ?? 'Date unknown' }}
                                        </span>
                                        @if ($record->bloodRequest)
                                            <span class="text-xs text-slate-500">
                                                for {{ $record->bloodRequest->patient_name }}
                                            </span>
                                        @endif
                                    </div>
                                    @if ($record->hospital)
                                        <p class="text-sm text-slate-400">🏥 {{ $record->hospital }}</p>
                                    @endif
                                    @if ($record->district)
                                        <p class="text-sm text-slate-500">📍 {{ $record->district }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    @if ($record->rating)
                                        <div class="rating-stars">
                                            {{ str_repeat('★', $record->rating) }}{{ str_repeat('☆', 5 - $record->rating) }}
                                        </div>
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $record->rating }}/5</div>
                                    @elseif ($record->blood_request_id && $record->bloodRequest?->requester_id)
                                        <a href="{{ route('feedback.show', $record) }}"
                                           class="btn-ghost text-xs" style="padding:5px 10px;">
                                            Rate Donor
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-600">No rating yet</span>
                                    @endif
                                </div>
                            </div>
                            @if ($record->feedback_notes)
                                <p class="mt-2 text-xs text-slate-400 italic border-t border-white/5 pt-2">
                                    "{{ $record->feedback_notes }}"
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Actions --}}
    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('live-feed') }}" class="btn-primary">🩸 See Blood Requests</a>
        <a href="{{ route('donor-search') }}" class="btn-ghost">🔍 Find Donors</a>
        <a href="{{ route('profile.edit') }}" class="btn-ghost">⚙️ Edit Profile</a>
    </div>
</div>

</body>
</html>
