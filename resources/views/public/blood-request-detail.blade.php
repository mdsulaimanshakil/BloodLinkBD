<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $bloodRequest->blood_group }} Blood Needed — {{ config('app.name', 'BloodLinkBD') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen bg-gray-100 py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

                {{-- Back link --}}
                <a href="{{ url('/') }}" class="text-sm text-indigo-600 hover:text-indigo-500 underline">
                    &larr; Back to home
                </a>

                {{-- Request Header --}}
                <div class="mt-4 bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800">
                                    🩸 {{ $bloodRequest->blood_group }} Blood Needed
                                </h1>
                                <p class="mt-1 text-sm text-gray-500">
                                    Posted {{ $bloodRequest->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                @if ($bloodRequest->urgency === 'critical') bg-red-100 text-red-800
                                @elseif ($bloodRequest->urgency === 'urgent') bg-orange-100 text-orange-800
                                @else bg-green-100 text-green-800
                                @endif">
                                {{ $bloodRequest->urgency_label }}
                            </span>
                        </div>

                        {{-- Request details --}}
                        <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="font-medium text-gray-500">Patient Name</dt>
                                <dd class="mt-1 text-gray-900">{{ $bloodRequest->patient_name }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Blood Group</dt>
                                <dd class="mt-1 text-red-600 font-bold text-lg">{{ $bloodRequest->blood_group }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">District</dt>
                                <dd class="mt-1 text-gray-900">{{ $bloodRequest->district }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Hospital</dt>
                                <dd class="mt-1 text-gray-900">{{ $bloodRequest->hospital }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $bloodRequest->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($bloodRequest->status) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Expires</dt>
                                <dd class="mt-1 text-gray-900">
                                    {{ $bloodRequest->expires_at ? $bloodRequest->expires_at->diffForHumans() : 'N/A' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Contact</dt>
                                <dd class="mt-1 text-gray-900">{{ $bloodRequest->masked_phone }}</dd>
                            </div>
                            @if ($bloodRequest->additional_notes)
                            <div class="sm:col-span-2">
                                <dt class="font-medium text-gray-500">Additional Notes</dt>
                                <dd class="mt-1 text-gray-900">{{ $bloodRequest->additional_notes }}</dd>
                            </div>
                            @endif
                        </dl>

                        {{-- WhatsApp contact link --}}
                        @if ($bloodRequest->status === 'active')
                        <div class="mt-6 flex flex-wrap gap-3 items-center">
                            <a href="{{ $whatsappLink }}" target="_blank" rel="noopener"
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-500 transition">
                                💬 Contact via WhatsApp
                            </a>

                            {{-- Prompt 13: "I Can Help" — visible only to verified, eligible donors --}}
                            @auth
                                @if (auth()->user()->donorProfile?->is_verified && auth()->user()->donorProfile?->is_available)
                                    @if ($alreadyResponded)
                                        <span class="inline-flex items-center px-4 py-2 bg-indigo-100 text-indigo-700 text-sm font-semibold rounded-md">
                                            ✅ You've already responded
                                        </span>
                                    @elseif ($bloodRequest->status === 'active')
                                        <form method="POST" action="{{ route('blood-requests.respond', $bloodRequest) }}">
                                            @csrf
                                            <button type="submit"
                                                    id="i-can-help-btn"
                                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-500 transition shadow-sm">
                                                🩸 I Can Help
                                            </button>
                                        </form>
                                    @endif
                                @elseif (auth()->user()->donorProfile && !auth()->user()->donorProfile->is_verified)
                                    <span class="text-xs text-gray-400">Verify your phone to respond to requests.</span>
                                @elseif (!auth()->user()->donorProfile)
                                    <a href="{{ route('donor.profile.create') }}"
                                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-500 transition">
                                        Complete Donor Profile to Help
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('login') }}"
                                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-500 transition">
                                    Log in to Help
                                </a>
                            @endauth
                        </div>

                        {{-- Flash messages --}}
                        @if (session('success'))
                            <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-md text-sm text-green-700">
                                ✅ {{ session('success') }}
                            </div>
                        @endif
                        @if (session('info'))
                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md text-sm text-blue-700">
                                ℹ️ {{ session('info') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-md text-sm text-red-700">
                                ❌ {{ session('error') }}
                            </div>
                        @endif
                        @endif
                    </div>
                </div>

                {{-- Compatible Donors Info --}}
                <div class="mt-6 bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-2">
                            Compatible Blood Groups for {{ $bloodRequest->blood_group }}
                        </h2>
                        <p class="text-sm text-gray-500 mb-4">
                            These blood groups can donate to a {{ $bloodRequest->blood_group }} recipient:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($compatibleDonors as $group)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold
                                    {{ $group === $bloodRequest->blood_group ? 'bg-red-100 text-red-800 ring-2 ring-red-300' : 'bg-indigo-100 text-indigo-800' }}">
                                    {{ $group }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Full Compatibility Chart --}}
                <div class="mt-6 bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">
                            Blood Compatibility Chart
                        </h2>
                        <p class="text-sm text-gray-500 mb-4">
                            Rows = Donor blood group &nbsp;→&nbsp; Columns = Recipient blood group.
                            <span class="text-green-600">✓</span> means the donor can give to that recipient.
                        </p>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm text-center">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600 border">Donor ↓ / Recipient →</th>
                                        @foreach ($bloodGroups as $recipient)
                                            <th class="px-3 py-2 font-semibold border
                                                {{ $recipient === $bloodRequest->blood_group ? 'bg-red-50 text-red-700' : 'text-gray-600' }}">
                                                {{ $recipient }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bloodGroups as $donor)
                                        <tr class="{{ in_array($donor, $compatibleDonors) ? 'bg-green-50' : '' }}">
                                            <td class="px-3 py-2 text-left font-semibold border
                                                {{ in_array($donor, $compatibleDonors) ? 'text-green-700' : 'text-gray-700' }}">
                                                {{ $donor }}
                                            </td>
                                            @foreach ($bloodGroups as $recipient)
                                                <td class="px-3 py-2 border
                                                    {{ $recipient === $bloodRequest->blood_group && in_array($donor, $compatibleDonors) ? 'bg-green-100' : '' }}">
                                                    @if ($compatibilityMatrix[$donor][$recipient])
                                                        <span class="text-green-600 font-bold">✓</span>
                                                    @else
                                                        <span class="text-gray-300">✗</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </body>
</html>
