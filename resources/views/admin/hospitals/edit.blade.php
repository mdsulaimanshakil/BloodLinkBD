<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Hospital — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Figtree', sans-serif; background: #0f172a; color: #e2e8f0; }
        .admin-nav { background: rgba(220,38,38,0.1); border-bottom: 1px solid rgba(220,38,38,0.2); padding: 12px 16px; }
        .form-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 32px; max-width: 640px; margin: 0 auto; }
        label { display:block; font-size:0.8rem; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px; }
        .form-input { width:100%; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#e2e8f0; border-radius:8px; padding:10px 14px; font-size:0.9rem; font-family:'Figtree',sans-serif; transition:border-color 0.2s; }
        .form-input:focus { outline:none; border-color:rgba(220,38,38,0.5); }
        .form-input option { background:#1e293b; }
        .error { color:#f87171; font-size:0.75rem; margin-top:4px; }
        .btn-primary { width:100%; background:linear-gradient(135deg,#dc2626,#b91c1c); color:white; border:none; border-radius:10px; padding:12px; font-weight:700; font-size:0.95rem; cursor:pointer; transition:all 0.2s; box-shadow:0 4px 16px rgba(220,38,38,0.3); }
        .btn-primary:hover { transform:translateY(-1px); }
        .btn-ghost { display:inline-flex;align-items:center; background:rgba(255,255,255,0.06); color:#94a3b8; border:1px solid rgba(255,255,255,0.1); border-radius:8px; padding:8px 14px; font-size:0.875rem; text-decoration:none; transition:all 0.2s; }
        .btn-ghost:hover { background:rgba(255,255,255,0.1); color:#e2e8f0; }
        #map-picker { height:280px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); margin-bottom:8px; }
        .tip { font-size:0.75rem; color:#64748b; margin-top:4px; }
    </style>
</head>
<body>
<div class="admin-nav">
    <div class="max-w-3xl mx-auto flex items-center gap-4">
        <a href="{{ route('admin.hospitals.index') }}" class="text-sm text-red-400 hover:text-red-300">&larr; Back to List</a>
        <span class="text-slate-500">|</span>
        <span class="text-sm font-semibold text-white">✏️ Edit Hospital</span>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="form-card">
        <h1 class="text-xl font-bold text-white mb-6">✏️ Edit Hospital</h1>

        <form method="POST" action="{{ route('admin.hospitals.update', $hospital) }}">
            @csrf @method('PUT')

            <div class="space-y-5">
                <div>
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" required
                           value="{{ old('name', $hospital->name) }}"
                           class="form-input">
                    @error('name') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="type">Type *</label>
                    <select id="type" name="type" required class="form-input">
                        <option value="hospital"   {{ old('type', $hospital->type) === 'hospital'   ? 'selected' : '' }}>🏥 Hospital</option>
                        <option value="blood_bank" {{ old('type', $hospital->type) === 'blood_bank' ? 'selected' : '' }}>🩸 Blood Bank</option>
                    </select>
                    @error('type') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="district">District *</label>
                    <select id="district" name="district" required class="form-input">
                        @foreach ($districts as $d)
                            <option value="{{ $d }}" {{ old('district', $hospital->district) === $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                    @error('district') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address"
                           value="{{ old('address', $hospital->address) }}"
                           class="form-input">
                    @error('address') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="contact">Phone / Contact</label>
                    <input type="text" id="contact" name="contact"
                           value="{{ old('contact', $hospital->contact) }}"
                           class="form-input">
                    @error('contact') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>Location on Map <span class="text-slate-600 font-normal normal-case">(click to move pin)</span></label>
                    <div id="map-picker"></div>
                    <p class="tip">Click anywhere on the map to update the latitude & longitude.</p>
                    <div class="grid grid-cols-2 gap-3 mt-2">
                        <div>
                            <label for="latitude">Latitude</label>
                            <input type="number" id="latitude" name="latitude" step="0.000001"
                                   value="{{ old('latitude', $hospital->latitude) }}"
                                   class="form-input">
                            @error('latitude') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="longitude">Longitude</label>
                            <input type="number" id="longitude" name="longitude" step="0.000001"
                                   value="{{ old('longitude', $hospital->longitude) }}"
                                   class="form-input">
                            @error('longitude') <p class="error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="submit" class="btn-primary" style="flex:1;">💾 Update Hospital</button>
                <a href="{{ route('admin.hospitals.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WPcU=" crossorigin=""></script>
<script>
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const initLat  = parseFloat(latInput.value) || 23.6850;
    const initLng  = parseFloat(lngInput.value) || 90.3563;
    const initZoom = latInput.value ? 14 : 7;
    const map      = L.map('map-picker').setView([initLat, initLng], initZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors', maxZoom: 19
    }).addTo(map);
    let marker = latInput.value ? L.marker([initLat, initLng]).addTo(map) : null;
    map.on('click', function(e) {
        const lat = e.latlng.lat.toFixed(6);
        const lng = e.latlng.lng.toFixed(6);
        latInput.value = lat; lngInput.value = lng;
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);
    });
    [latInput, lngInput].forEach(input => {
        input.addEventListener('change', () => {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                map.setView([lat, lng], 14);
                if (marker) map.removeLayer(marker);
                marker = L.marker([lat, lng]).addTo(map);
            }
        });
    });
</script>
</body>
</html>
