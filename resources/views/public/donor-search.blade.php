<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Find a Blood Donor — {{ config('app.name', 'BloodLinkBD') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen bg-gray-100 py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

                {{-- Header --}}
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <a href="{{ url('/') }}" class="text-sm text-indigo-600 hover:text-indigo-500 underline">&larr; Back to home</a>
                        <h1 class="mt-2 text-2xl font-bold text-gray-800">🔍 Find a Blood Donor</h1>
                        <p class="text-sm text-gray-500">Search for verified, available donors in your area.</p>
                    </div>
                    <a href="{{ route('blood-requests.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-500 transition">
                        🩸 Post Emergency Request
                    </a>
                </div>

                {{-- Filters --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-6">
                    <form method="GET" action="{{ route('donor-search') }}" class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-[180px]">
                            <label for="blood_group" class="block text-sm font-medium text-gray-700 mb-1">Blood Group</label>
                            <select id="blood_group" name="blood_group"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="">All Blood Groups</option>
                                @foreach ($bloodGroups as $group)
                                    <option value="{{ $group }}" {{ ($filters['blood_group'] ?? '') === $group ? 'selected' : '' }}>
                                        {{ $group }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-[180px]">
                            <label for="district" class="block text-sm font-medium text-gray-700 mb-1">District</label>
                            <select id="district" name="district"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="">All Districts</option>
                                @foreach ($districts as $district)
                                    <option value="{{ $district }}" {{ ($filters['district'] ?? '') === $district ? 'selected' : '' }}>
                                        {{ $district }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-500 transition">
                                Search
                            </button>
                            @if (($filters['blood_group'] ?? '') || ($filters['district'] ?? ''))
                                <a href="{{ route('donor-search') }}"
                                   class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-semibold rounded-md hover:bg-gray-300 transition">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- Results --}}
                @if ($donors->isEmpty())
                    <div class="bg-white shadow-sm sm:rounded-lg p-12 text-center">
                        <div class="text-4xl mb-3">🔍</div>
                        <h3 class="text-lg font-semibold text-gray-700">No donors found</h3>
                        <p class="text-sm text-gray-500 mt-1">Try adjusting your filters or check back later.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($donors as $donor)
                            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                                <div class="p-5">
                                    {{-- Header row --}}
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-800">
                                            {{ $donor->blood_group }}
                                        </span>
                                        <div class="flex items-center gap-1.5">
                                            @if ($donor->is_trusted)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800"
                                                      title="3+ donations">
                                                    ⭐ Trusted
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Available
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Donor info --}}
                                    <h3 class="text-base font-semibold text-gray-800">
                                        {{ $donor->user->name ?? 'Anonymous Donor' }}
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">📍 {{ $donor->district }}</p>

                                    <dl class="mt-3 space-y-1 text-sm">
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500">Donations</dt>
                                            <dd class="font-medium text-gray-900">{{ $donor->donation_count }}</dd>
                                        </div>
                                        @if ($donor->days_since_last_donation !== null)
                                            <div class="flex justify-between">
                                                <dt class="text-gray-500">Last donated</dt>
                                                <dd class="text-gray-900">{{ $donor->days_since_last_donation }} days ago</dd>
                                            </div>
                                        @endif
                                    </dl>

                                    {{-- Phone / Contact --}}
                                    <div class="mt-4 pt-3 border-t border-gray-100">
                                        @if ($canRevealPhone)
                                            <p class="text-sm text-gray-700">
                                                📞 <span class="font-medium">{{ $donor->phone }}</span>
                                            </p>
                                            <a href="{{ \App\Services\Notifications\WhatsAppNotificationChannel::whatsappLink($donor->phone, 'Hi, I found you on BloodLinkBD. I need ' . $donor->blood_group . ' blood. Are you available to donate?') }}"
                                               target="_blank" rel="noopener"
                                               class="mt-2 inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-md hover:bg-green-500 transition">
                                                💬 WhatsApp
                                            </a>
                                        @else
                                            <p class="text-sm text-gray-500">
                                                📞 {{ $donor->masked_phone }}
                                            </p>
                                            <p class="mt-1 text-xs text-gray-400">
                                                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500 underline">Log in</a>
                                                with a verified profile to see full number & WhatsApp link.
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $donors->links() }}
                    </div>
                @endif

            </div>
        </div>
    </body>
</html>
