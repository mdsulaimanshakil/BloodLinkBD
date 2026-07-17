<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Request Posted — {{ config('app.name', 'BloodLinkBD') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12">
            <div class="max-w-lg w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8 text-center">

                        <div class="text-5xl mb-4">✅</div>

                        <h1 class="text-2xl font-bold text-gray-800">
                            Request Posted Successfully!
                        </h1>

                        <p class="mt-3 text-sm text-gray-600">
                            Your emergency blood request has been posted and is now visible to donors.
                        </p>

                        {{-- Request Summary --}}
                        <div class="mt-6 bg-gray-50 rounded-lg p-4 text-left text-sm">
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="font-medium text-gray-500">Patient</dt>
                                    <dd class="text-gray-900">{{ $bloodRequest->patient_name }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-medium text-gray-500">Blood Group</dt>
                                    <dd class="text-gray-900 font-bold text-red-600">{{ $bloodRequest->blood_group }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-medium text-gray-500">District</dt>
                                    <dd class="text-gray-900">{{ $bloodRequest->district }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-medium text-gray-500">Hospital</dt>
                                    <dd class="text-gray-900">{{ $bloodRequest->hospital }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-medium text-gray-500">Urgency</dt>
                                    <dd>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if ($bloodRequest->urgency === 'critical') bg-red-100 text-red-800
                                            @elseif ($bloodRequest->urgency === 'urgent') bg-orange-100 text-orange-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ $bloodRequest->urgency_label }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-medium text-gray-500">Expires</dt>
                                    <dd class="text-gray-900">{{ $bloodRequest->expires_at->diffForHumans() }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-medium text-gray-500">Contact</dt>
                                    <dd class="text-gray-900">{{ $bloodRequest->masked_phone }}</dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Actions --}}
                        <div class="mt-6 space-y-3">
                            <a href="{{ url('/') }}"
                               class="block w-full px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-500 transition">
                                ← Back to Home
                            </a>
                            <a href="{{ route('blood-requests.create') }}"
                               class="block w-full px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-md hover:bg-gray-50 transition">
                                Post Another Request
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
