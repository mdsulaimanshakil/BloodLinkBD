<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin — Hospitals & Blood Banks — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Figtree', sans-serif; background: #0f172a; color: #e2e8f0; }
        .admin-nav { background: rgba(220,38,38,0.1); border-bottom: 1px solid rgba(220,38,38,0.2); padding: 12px 16px; }
        .card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; }
        .btn-primary { display:inline-flex;align-items:center;gap:6px; background:linear-gradient(135deg,#dc2626,#b91c1c); color:white; border:none; border-radius:8px; padding:8px 16px; font-weight:600; font-size:0.875rem; text-decoration:none; cursor:pointer; transition:all 0.2s; }
        .btn-primary:hover { transform:translateY(-1px); }
        .btn-ghost { display:inline-flex;align-items:center;gap:4px; background:rgba(255,255,255,0.06); color:#94a3b8; border:1px solid rgba(255,255,255,0.1); border-radius:6px; padding:5px 10px; font-size:0.75rem; text-decoration:none; transition:all 0.2s; }
        .btn-ghost:hover { background:rgba(255,255,255,0.1); color:#e2e8f0; }
        .btn-danger { display:inline-flex;align-items:center; background:rgba(220,38,38,0.15); color:#fca5a5; border:1px solid rgba(220,38,38,0.3); border-radius:6px; padding:5px 10px; font-size:0.75rem; cursor:pointer; transition:all 0.2s; }
        .btn-danger:hover { background:rgba(220,38,38,0.25); }
        .filter-select { background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#e2e8f0; border-radius:8px; padding:8px 12px; font-size:0.875rem; }
        .filter-select option { background:#1e293b; }
        th { font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#64748b; padding:10px 16px; text-align:left; border-bottom:1px solid rgba(255,255,255,0.08); }
        td { padding:12px 16px; font-size:0.875rem; border-bottom:1px solid rgba(255,255,255,0.04); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background:rgba(255,255,255,0.02); }
        .type-badge { display:inline-flex;align-items:center;gap:4px; padding:2px 8px; border-radius:100px; font-size:0.65rem; font-weight:700; text-transform:uppercase; }
        .type-hospital   { background:rgba(37,99,235,0.2); color:#93c5fd; border:1px solid rgba(37,99,235,0.3); }
        .type-blood_bank { background:rgba(220,38,38,0.2); color:#fca5a5; border:1px solid rgba(220,38,38,0.3); }
    </style>
</head>
<body>

<div class="admin-nav">
    <div class="max-w-6xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-sm text-red-400 hover:text-red-300">&larr; Dashboard</a>
            <span class="text-slate-500">|</span>
            <span class="text-sm font-semibold text-white">🏥 Hospital Management</span>
        </div>
        <a href="{{ route('hospitals.index') }}" class="btn-ghost">👁 Public View</a>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-8">

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-900/30 border border-green-500/30 text-green-300 text-sm">✅ {{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold text-white">Hospitals & Blood Banks</h1>
        <a href="{{ route('admin.hospitals.create') }}" class="btn-primary">➕ Add New</a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.hospitals.index') }}" class="flex flex-wrap gap-3 mb-6">
        <select name="district" class="filter-select" onchange="this.form.submit()">
            <option value="">All Districts</option>
            @foreach ($districts as $d)
                <option value="{{ $d }}" {{ ($filters['district'] ?? '') === $d ? 'selected' : '' }}>{{ $d }}</option>
            @endforeach
        </select>
        <select name="type" class="filter-select" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="hospital"   {{ ($filters['type'] ?? '') === 'hospital'   ? 'selected' : '' }}>🏥 Hospital</option>
            <option value="blood_bank" {{ ($filters['type'] ?? '') === 'blood_bank' ? 'selected' : '' }}>🩸 Blood Bank</option>
        </select>
        @if (($filters['district'] ?? '') || ($filters['type'] ?? ''))
            <a href="{{ route('admin.hospitals.index') }}" class="btn-ghost">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="w-full">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>District</th>
                    <th>Contact</th>
                    <th>Map</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($hospitals as $hospital)
                    <tr>
                        <td>
                            <div class="font-medium text-white">{{ $hospital->name }}</div>
                            @if ($hospital->address)
                                <div class="text-xs text-slate-500 mt-0.5">{{ Str::limit($hospital->address, 50) }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="type-badge type-{{ $hospital->type }}">
                                {{ $hospital->type_label }}
                            </span>
                        </td>
                        <td class="text-slate-300">{{ $hospital->district }}</td>
                        <td class="text-slate-400 font-mono text-xs">{{ $hospital->contact ?? '—' }}</td>
                        <td>
                            @if ($hospital->has_coordinates)
                                <span class="text-green-400 text-xs">✓ {{ number_format($hospital->latitude, 4) }}, {{ number_format($hospital->longitude, 4) }}</span>
                            @else
                                <span class="text-slate-600 text-xs">No coords</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.hospitals.edit', $hospital) }}" class="btn-ghost">✏️ Edit</a>
                                <form method="POST" action="{{ route('admin.hospitals.destroy', $hospital) }}"
                                      onsubmit="return confirm('Delete {{ addslashes($hospital->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-danger">🗑</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500 py-12">No hospitals found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($hospitals->hasPages())
            <div class="p-4 border-t border-white/5">
                {{ $hospitals->links() }}
            </div>
        @endif
    </div>
</div>
</body>
</html>
