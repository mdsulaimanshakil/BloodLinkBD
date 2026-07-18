<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Hospital & Blood Bank Directory — {{ config('app.name', 'BloodLinkBD') }}</title>
    <meta name="description" content="Find hospitals and blood banks near you across Bangladesh. Interactive map powered by OpenStreetMap.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Leaflet.js — no API key required (OpenStreetMap) --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Figtree', sans-serif; background: #0f172a; color: #e2e8f0; }

        .hero-section {
            background: linear-gradient(135deg, #0f172a 0%, #0d2137 50%, #0f172a 100%);
            border-bottom: 1px solid rgba(59,130,246,0.2);
            padding: 48px 16px;
        }

        .section-title {
            font-size: 2rem; font-weight: 800; color: white;
        }

        .filter-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 20px;
        }

        .filter-select {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            color: #e2e8f0; border-radius: 8px;
            padding: 9px 12px; font-size: 0.875rem; width: 100%;
            transition: border-color 0.2s;
        }
        .filter-select:focus { outline: none; border-color: rgba(59,130,246,0.5); }
        .filter-select option { background: #1e293b; }

        .filter-btn {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white; border: none; border-radius: 8px;
            padding: 9px 20px; font-weight: 600; font-size: 0.875rem;
            cursor: pointer; transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
        }
        .filter-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(37,99,235,0.4); }

        .clear-btn {
            background: rgba(255,255,255,0.06); color: #94a3b8;
            border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;
            padding: 9px 14px; font-size: 0.875rem; text-decoration: none;
            transition: all 0.2s;
        }
        .clear-btn:hover { background: rgba(255,255,255,0.1); color: #e2e8f0; }

        /* Map */
        #map {
            height: 420px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.1);
            overflow: hidden;
            position: relative;
            z-index: 0;
        }

        /* Hospital Cards */
        .hospital-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.25s;
            position: relative;
        }
        .hospital-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0;
            height: 3px; border-radius: 12px 12px 0 0;
        }
        .hospital-card.type-hospital::before    { background: linear-gradient(90deg, #2563eb, #3b82f6); }
        .hospital-card.type-blood_bank::before  { background: linear-gradient(90deg, #dc2626, #ef4444); }
        .hospital-card:hover {
            background: rgba(255,255,255,0.07);
            border-color: rgba(255,255,255,0.14);
            transform: translateY(-2px);
        }

        .type-pill {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 100px;
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;
        }
        .type-hospital   { background: rgba(37,99,235,0.2); color: #93c5fd; border: 1px solid rgba(37,99,235,0.3); }
        .type-blood_bank { background: rgba(220,38,38,0.2); color: #fca5a5; border: 1px solid rgba(220,38,38,0.3); }

        .contact-link {
            display: inline-flex; align-items: center; gap: 5px;
            margin-top: 10px; padding: 6px 12px;
            background: rgba(16,185,129,0.15); color: #6ee7b7;
            border: 1px solid rgba(16,185,129,0.3);
            border-radius: 8px; font-size: 0.75rem; font-weight: 600;
            text-decoration: none; transition: all 0.2s;
        }
        .contact-link:hover { background: rgba(16,185,129,0.25); }

        .map-btn {
            display: inline-flex; align-items: center; gap: 5px;
            margin-top: 10px; margin-left: 6px; padding: 6px 12px;
            background: rgba(59,130,246,0.15); color: #93c5fd;
            border: 1px solid rgba(59,130,246,0.3);
            border-radius: 8px; font-size: 0.75rem; font-weight: 600;
            text-decoration: none; transition: all 0.2s;
        }
        .map-btn:hover { background: rgba(59,130,246,0.25); }

        .empty-state { text-align: center; padding: 80px 20px; }
        .empty-icon  { font-size: 4rem; opacity: 0.4; margin-bottom: 16px; }

        .section-label {
            font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: #64748b; margin-bottom: 12px;
        }

        .stat-chip {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 100px; padding: 4px 12px;
            font-size: 0.75rem; color: #94a3b8;
        }

        .admin-bar {
            background: rgba(220,38,38,0.1); border-bottom: 1px solid rgba(220,38,38,0.2);
            padding: 8px 16px; text-align: center;
        }
    </style>
</head>
<body>

    {{-- Admin bar --}}
    @auth
        @if (auth()->user()->isAdmin())
            <div class="admin-bar">
                <span class="text-red-300 text-sm">Admin View</span>
                <a href="{{ route('admin.hospitals.index') }}"
                   class="ml-4 text-xs text-red-400 underline hover:text-red-300">Manage Hospitals →</a>
            </div>
        @endif
    @endauth

    {{-- Hero --}}
    <div class="hero-section">
        <div class="max-w-6xl mx-auto">
            <a href="{{ url('/') }}" class="text-sm text-blue-400 hover:text-blue-300 mb-4 inline-block">&larr; Back to home</a>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="section-title">🏥 Hospital & Blood Bank Directory</h1>
                    <p class="text-slate-400 mt-1">Find hospitals and blood banks across Bangladesh on the map.</p>
                </div>
                <div class="flex gap-2">
                    <span class="stat-chip">🏥 {{ $hospitals->total() }} locations</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-8">

        {{-- Filters --}}
        <div class="filter-card mb-6">
            <form method="GET" action="{{ route('hospitals.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wide">District</label>
                    <select name="district" class="filter-select">
                        <option value="">All Districts</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district }}" {{ ($filters['district'] ?? '') === $district ? 'selected' : '' }}>
                                {{ $district }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wide">Type</label>
                    <select name="type" class="filter-select">
                        <option value="">All Types</option>
                        <option value="hospital"   {{ ($filters['type'] ?? '') === 'hospital'   ? 'selected' : '' }}>🏥 Hospital</option>
                        <option value="blood_bank" {{ ($filters['type'] ?? '') === 'blood_bank' ? 'selected' : '' }}>🩸 Blood Bank</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="filter-btn">Filter</button>
                    @if (($filters['district'] ?? '') || ($filters['type'] ?? ''))
                        <a href="{{ route('hospitals.index') }}" class="clear-btn">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Leaflet.js Map --}}
        <div class="mb-8">
            <div class="section-label">📍 Map View (OpenStreetMap · No API key)</div>
            <div id="map"></div>
        </div>

        {{-- Directory List --}}
        <div class="section-label">List View</div>

        @if ($hospitals->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">🏥</div>
                <h3 class="text-lg font-semibold text-slate-300">No locations found</h3>
                <p class="text-slate-500 text-sm mt-1">Try clearing the filters to see all hospitals.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($hospitals as $hospital)
                    <div class="hospital-card type-{{ $hospital->type }}" id="card-{{ $hospital->id }}">
                        {{-- Type + District --}}
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="type-pill type-{{ $hospital->type }}">
                                {{ $hospital->type === 'blood_bank' ? '🩸 Blood Bank' : '🏥 Hospital' }}
                            </span>
                            <span class="text-xs text-slate-500">📍 {{ $hospital->district }}</span>
                        </div>

                        {{-- Name + Address --}}
                        <h3 class="font-semibold text-white text-base leading-snug">
                            {{ $hospital->name }}
                        </h3>
                        @if ($hospital->address)
                            <p class="text-slate-400 text-sm mt-1">{{ $hospital->address }}</p>
                        @endif

                        {{-- Actions --}}
                        <div class="flex flex-wrap gap-1 mt-2">
                            @if ($hospital->contact)
                                <a href="tel:{{ $hospital->contact }}" class="contact-link">
                                    📞 {{ $hospital->contact }}
                                </a>
                            @endif
                            @if ($hospital->has_coordinates)
                                <button onclick="focusOnMap({{ $hospital->latitude }}, {{ $hospital->longitude }}, '{{ addslashes($hospital->name) }}')"
                                        class="map-btn" type="button">
                                    🗺️ Map
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $hospitals->links() }}
            </div>
        @endif
    </div>

    {{-- Leaflet.js Script --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WPcU=" crossorigin=""></script>

    <script>
        // Map data from PHP
        const hospitals = @json($mapData);

        // Determine map center: Bangladesh center, or first hospital if filtered
        let mapCenter = [23.6850, 90.3563]; // Bangladesh center
        let mapZoom   = 7;

        if (hospitals.length === 1) {
            mapCenter = [hospitals[0].latitude, hospitals[0].longitude];
            mapZoom   = 14;
        } else if (hospitals.length > 0 && hospitals.length <= 5) {
            mapCenter = [hospitals[0].latitude, hospitals[0].longitude];
            mapZoom   = 10;
        }

        // Init Leaflet map
        const map = L.map('map').setView(mapCenter, mapZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(map);

        // Custom icons
        const hospitalIcon = L.divIcon({
            html: '<div style="background:#2563eb;border:2px solid white;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:14px;box-shadow:0 2px 8px rgba(0,0,0,0.4);">🏥</div>',
            className: '',
            iconSize: [28, 28],
            iconAnchor: [14, 14],
        });

        const bloodBankIcon = L.divIcon({
            html: '<div style="background:#dc2626;border:2px solid white;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:14px;box-shadow:0 2px 8px rgba(0,0,0,0.4);">🩸</div>',
            className: '',
            iconSize: [28, 28],
            iconAnchor: [14, 14],
        });

        // Add markers
        const markers = {};
        hospitals.forEach(h => {
            const icon   = h.type === 'blood_bank' ? bloodBankIcon : hospitalIcon;
            const marker = L.marker([h.latitude, h.longitude], { icon })
                .addTo(map)
                .bindPopup(`
                    <div style="font-family:sans-serif;min-width:180px;">
                        <strong style="font-size:14px;">${h.name}</strong><br>
                        <span style="color:#64748b;font-size:12px;">${h.type === 'blood_bank' ? '🩸 Blood Bank' : '🏥 Hospital'}</span><br>
                        <span style="font-size:12px;">📍 ${h.district}</span><br>
                        ${h.address ? `<span style="font-size:11px;color:#475569;">${h.address}</span><br>` : ''}
                        ${h.contact ? `<a href="tel:${h.contact}" style="font-size:12px;color:#2563eb;">📞 ${h.contact}</a>` : ''}
                    </div>
                `);
            markers[`${h.latitude},${h.longitude}`] = marker;
        });

        // Focus on a specific map point from card button
        function focusOnMap(lat, lng, name) {
            map.flyTo([lat, lng], 15, { duration: 1.2 });
            const key = `${lat},${lng}`;
            if (markers[key]) {
                setTimeout(() => markers[key].openPopup(), 1300);
            }
            document.getElementById('map').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    </script>
</body>
</html>
