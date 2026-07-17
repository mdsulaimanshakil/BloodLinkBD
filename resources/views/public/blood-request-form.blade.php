<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Emergency Blood Request — {{ config('app.name', 'BloodLinkBD') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- reCAPTCHA v2 -->
        @if ($recaptchaSiteKey)
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        @endif
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen bg-gray-100 py-8">

            {{-- Header --}}
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                <a href="{{ url('/') }}" class="text-sm text-indigo-600 hover:text-indigo-500 underline">
                    &larr; Back to home
                </a>
                <h1 class="mt-2 text-2xl font-bold text-gray-800">
                    🩸 Emergency Blood Request
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    No account needed. Fill in the details below and your request will be visible to all available donors in your district.
                </p>
            </div>

            {{-- Form Card --}}
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        {{-- Global success/error flash --}}
                        @if (session('success'))
                            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-md text-sm">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('blood-requests.store') }}">
                            @csrf

                            {{-- Patient Name --}}
                            <div>
                                <label for="patient_name" class="block text-sm font-medium text-gray-700">Patient Name</label>
                                <input id="patient_name" type="text" name="patient_name"
                                       value="{{ old('patient_name') }}" required
                                       class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                       placeholder="Enter patient's full name">
                                @error('patient_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Blood Group --}}
                            <div class="mt-4">
                                <label for="blood_group" class="block text-sm font-medium text-gray-700">Blood Group Needed</label>
                                <select id="blood_group" name="blood_group" required
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select Blood Group</option>
                                    @foreach ($bloodGroups as $group)
                                        <option value="{{ $group }}" {{ old('blood_group') === $group ? 'selected' : '' }}>
                                            {{ $group }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('blood_group')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- District --}}
                            <div class="mt-4">
                                <label for="district" class="block text-sm font-medium text-gray-700">District</label>
                                <select id="district" name="district" required
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select District</option>
                                    @foreach ($districts as $district)
                                        <option value="{{ $district }}" {{ old('district') === $district ? 'selected' : '' }}>
                                            {{ $district }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('district')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Hospital --}}
                            <div class="mt-4">
                                <label for="hospital" class="block text-sm font-medium text-gray-700">Hospital / Clinic Name</label>
                                <input id="hospital" type="text" name="hospital"
                                       value="{{ old('hospital') }}" required
                                       class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                       placeholder="e.g. Dhaka Medical College Hospital">
                                @error('hospital')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Urgency --}}
                            <div class="mt-4">
                                <label for="urgency" class="block text-sm font-medium text-gray-700">Urgency Level</label>
                                <select id="urgency" name="urgency" required
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach ($urgencies as $value => $label)
                                        <option value="{{ $value }}" {{ old('urgency', 'normal') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                            @if ($value === 'critical') — expires in 48h @endif
                                            @if ($value === 'urgent') — expires in 4 days @endif
                                            @if ($value === 'normal') — expires in 7 days @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('urgency')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Requester Phone --}}
                            <div class="mt-4">
                                <label for="requester_phone" class="block text-sm font-medium text-gray-700">Your Phone Number</label>
                                <input id="requester_phone" type="tel" name="requester_phone"
                                       value="{{ old('requester_phone') }}" required
                                       class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                       placeholder="01712345678">
                                <p class="mt-1 text-xs text-gray-500">Bangladesh mobile number. Max 3 active requests per phone number.</p>
                                @error('requester_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Additional Notes (optional) --}}
                            <div class="mt-4">
                                <label for="additional_notes" class="block text-sm font-medium text-gray-700">Additional Notes (Optional)</label>
                                <textarea id="additional_notes" name="additional_notes" rows="3"
                                          class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                          placeholder="Any additional details about the patient or request...">{{ old('additional_notes') }}</textarea>
                                @error('additional_notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- reCAPTCHA v2 --}}
                            <div class="mt-4">
                                @if ($recaptchaSiteKey)
                                    <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
                                    @error('g-recaptcha-response')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                @else
                                    <p class="text-xs text-yellow-600 bg-yellow-50 border border-yellow-200 rounded-md p-2">
                                        ⚠️ reCAPTCHA is not configured. Set <code>RECAPTCHA_SITE_KEY</code> and <code>RECAPTCHA_SECRET_KEY</code> in your <code>.env</code> file.
                                    </p>
                                @endif
                            </div>

                            {{-- Submit --}}
                            <div class="mt-6">
                                <button type="submit"
                                        class="w-full inline-flex justify-center items-center px-4 py-3 bg-red-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    🩸 Post Emergency Request
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </body>
</html>
