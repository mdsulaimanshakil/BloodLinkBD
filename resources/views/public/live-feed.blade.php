<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Live Blood Request Feed — {{ config('app.name', 'BloodLinkBD') }}</title>
        <meta name="description" content="Real-time blood donation requests sorted by urgency. Find critical, urgent, and normal blood requests across Bangladesh.">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --red-primary: #dc2626;
                --red-dark: #991b1b;
                --red-glow: rgba(220, 38, 38, 0.3);
            }

            body { font-family: 'Figtree', sans-serif; background: #0f172a; color: #e2e8f0; }

            .live-hero {
                background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
                border-bottom: 1px solid rgba(220, 38, 38, 0.2);
            }

            .pulse-dot {
                animation: pulse-ring 1.5s ease-in-out infinite;
            }

            @keyframes pulse-ring {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.5; transform: scale(0.85); }
            }

            .live-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: rgba(220, 38, 38, 0.15);
                border: 1px solid rgba(220, 38, 38, 0.4);
                color: #fca5a5;
                padding: 4px 12px;
                border-radius: 100px;
                font-size: 0.75rem;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .filter-card {
                background: rgba(255,255,255,0.04);
                border: 1px solid rgba(255,255,255,0.08);
                border-radius: 12px;
                backdrop-filter: blur(8px);
            }

            .filter-select {
                background: rgba(255,255,255,0.06);
                border: 1px solid rgba(255,255,255,0.12);
                color: #e2e8f0;
                border-radius: 8px;
                padding: 8px 12px;
                font-size: 0.875rem;
                transition: border-color 0.2s;
                width: 100%;
            }

            .filter-select:focus {
                outline: none;
                border-color: rgba(220, 38, 38, 0.6);
                background: rgba(255,255,255,0.08);
            }

            .filter-select option { background: #1e293b; }

            .filter-btn {
                background: linear-gradient(135deg, #dc2626, #b91c1c);
                color: white;
                border: none;
                border-radius: 8px;
                padding: 9px 20px;
                font-weight: 600;
                font-size: 0.875rem;
                cursor: pointer;
                transition: all 0.2s;
                box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
            }

            .filter-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(220, 38, 38, 0.4); }

            .clear-btn {
                background: rgba(255,255,255,0.06);
                color: #94a3b8;
                border: 1px solid rgba(255,255,255,0.1);
                border-radius: 8px;
                padding: 9px 16px;
                font-size: 0.875rem;
                text-decoration: none;
                transition: all 0.2s;
            }

            .clear-btn:hover { background: rgba(255,255,255,0.1); color: #e2e8f0; }

            /* Request Cards */
            #feed-container { min-height: 400px; }

            .request-card {
                background: rgba(255,255,255,0.04);
                border: 1px solid rgba(255,255,255,0.08);
                border-radius: 12px;
                padding: 20px;
                transition: all 0.25s ease;
                position: relative;
                overflow: hidden;
            }

            .request-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 3px;
                border-radius: 12px 12px 0 0;
            }

            .request-card.urgency-critical::before { background: linear-gradient(90deg, #dc2626, #ef4444); }
            .request-card.urgency-urgent::before { background: linear-gradient(90deg, #d97706, #f59e0b); }
            .request-card.urgency-normal::before { background: linear-gradient(90deg, #059669, #10b981); }

            .request-card:hover {
                background: rgba(255,255,255,0.07);
                border-color: rgba(255,255,255,0.14);
                transform: translateY(-2px);
                box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            }

            .urgency-badge {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 3px 10px;
                border-radius: 100px;
                font-size: 0.7rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.06em;
            }

            .urgency-critical { background: rgba(220, 38, 38, 0.2); color: #fca5a5; border: 1px solid rgba(220,38,38,0.3); }
            .urgency-urgent   { background: rgba(217, 119, 6, 0.2);  color: #fcd34d; border: 1px solid rgba(217,119,6,0.3); }
            .urgency-normal   { background: rgba(5, 150, 105, 0.2);  color: #6ee7b7; border: 1px solid rgba(5,150,105,0.3); }

            .blood-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 48px;
                height: 48px;
                background: linear-gradient(135deg, rgba(220,38,38,0.25), rgba(185,28,28,0.15));
                border: 1.5px solid rgba(220,38,38,0.4);
                border-radius: 12px;
                font-size: 1rem;
                font-weight: 800;
                color: #fca5a5;
                flex-shrink: 0;
            }

            .countdown-timer {
                font-size: 0.7rem;
                color: #64748b;
                font-variant-numeric: tabular-nums;
            }

            .refresh-indicator {
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: 0.75rem;
                color: #64748b;
            }

            .refresh-dot {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background: #10b981;
                animation: blink 2s ease-in-out infinite;
            }

            @keyframes blink {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.2; }
            }

            .empty-state {
                text-align: center;
                padding: 80px 20px;
            }

            .empty-icon { font-size: 4rem; margin-bottom: 16px; opacity: 0.5; }

            .view-detail-link {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                margin-top: 14px;
                padding: 8px 16px;
                background: linear-gradient(135deg, rgba(220,38,38,0.15), rgba(185,28,28,0.1));
                border: 1px solid rgba(220,38,38,0.3);
                border-radius: 8px;
                color: #fca5a5;
                text-decoration: none;
                font-size: 0.8rem;
                font-weight: 600;
                transition: all 0.2s;
            }

            .view-detail-link:hover {
                background: rgba(220,38,38,0.25);
                border-color: rgba(220,38,38,0.5);
                transform: translateY(-1px);
            }

            .stat-pill {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                font-size: 0.75rem;
                color: #94a3b8;
            }

            .count-badge {
                background: rgba(220,38,38,0.2);
                color: #fca5a5;
                border-radius: 100px;
                padding: 2px 8px;
                font-size: 0.7rem;
                font-weight: 700;
            }
        </style>
    </head>

    <body>
        {{-- Hero --}}
        <div class="live-hero py-10 px-4">
            <div class="max-w-5xl mx-auto">
                <a href="{{ url('/') }}" class="text-sm text-red-400 hover:text-red-300 transition mb-4 inline-block">&larr; Back to home</a>

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-3">
                            <span class="live-badge">
                                <span class="pulse-dot inline-block w-2 h-2 rounded-full bg-red-400"></span>
                                Live Feed
                            </span>
                            <span id="request-count" class="count-badge">
                                {{ $requests->total() }} active
                            </span>
                        </div>
                        <h1 class="text-3xl font-bold text-white">🩸 Blood Request Feed</h1>
                        <p class="text-slate-400 mt-1">Critical requests first · Auto-refreshes every 30 seconds</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="refresh-indicator">
                            <span class="refresh-dot"></span>
                            <span id="refresh-countdown">Refreshing in 30s</span>
                        </div>
                        <a href="{{ route('blood-requests.create') }}"
                           class="filter-btn">🆘 Post Request</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="max-w-5xl mx-auto px-4 py-6">
            <div class="filter-card p-4">
                <form method="GET" action="{{ route('live-feed') }}" class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[160px]">
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wide">Blood Group</label>
                        <select id="blood_group" name="blood_group" class="filter-select">
                            <option value="">All Blood Groups</option>
                            @foreach ($bloodGroups as $group)
                                <option value="{{ $group }}" {{ ($filters['blood_group'] ?? '') === $group ? 'selected' : '' }}>
                                    {{ $group }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[160px]">
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wide">District</label>
                        <select id="district" name="district" class="filter-select">
                            <option value="">All Districts</option>
                            @foreach ($districts as $district)
                                <option value="{{ $district }}" {{ ($filters['district'] ?? '') === $district ? 'selected' : '' }}>
                                    {{ $district }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="filter-btn">Filter</button>
                        @if (($filters['blood_group'] ?? '') || ($filters['district'] ?? ''))
                            <a href="{{ route('live-feed') }}" class="clear-btn">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Feed --}}
        <div class="max-w-5xl mx-auto px-4 pb-16">
            <div id="feed-container">
                @include('public.partials.feed-cards', ['requests' => $requests])
            </div>

            {{-- Pagination --}}
            <div class="mt-6" id="pagination-area">
                @if ($requests->hasPages())
                    <div class="flex justify-center">
                        {{ $requests->links() }}
                    </div>
                @endif
            </div>
        </div>

        <script>
            // Auto-refresh every 30 seconds via polling
            (function () {
                let countdown = 30;
                const countdownEl = document.getElementById('refresh-countdown');
                const feedContainer = document.getElementById('feed-container');
                const requestCountEl = document.getElementById('request-count');

                function updateCountdown() {
                    countdown--;
                    if (countdownEl) {
                        countdownEl.textContent = countdown <= 0 ? 'Refreshing…' : `Refreshing in ${countdown}s`;
                    }
                    if (countdown <= 0) {
                        fetchFeed();
                    }
                }

                function fetchFeed() {
                    const params = new URLSearchParams(window.location.search);
                    fetch(`{{ route('live-feed.poll') }}?` + params.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (feedContainer && data.html) {
                            feedContainer.innerHTML = data.html;
                        }
                        if (requestCountEl && data.count !== undefined) {
                            requestCountEl.textContent = data.count + ' active';
                        }
                        countdown = 30;
                        if (countdownEl) countdownEl.textContent = 'Refreshing in 30s';
                    })
                    .catch(() => {
                        countdown = 30;
                        if (countdownEl) countdownEl.textContent = 'Refreshing in 30s';
                    });
                }

                setInterval(updateCountdown, 1000);
            })();
        </script>
    </body>
</html>
