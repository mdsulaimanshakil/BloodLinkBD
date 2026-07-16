<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Complete Your Donor Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <p class="mb-6 text-sm text-gray-600">
                        Please complete your donor profile to start receiving blood donation requests in your area.
                        Your phone number will be verified via OTP.
                    </p>

                    <form method="POST" action="{{ route('donor.profile.store') }}">
                        @csrf

                        <!-- Blood Group -->
                        <div>
                            <x-input-label for="blood_group" :value="__('Blood Group')" />
                            <select id="blood_group" name="blood_group"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required>
                                <option value="">{{ __('Select Blood Group') }}</option>
                                @foreach ($bloodGroups as $group)
                                    <option value="{{ $group }}" {{ old('blood_group') === $group ? 'selected' : '' }}>
                                        {{ $group }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('blood_group')" class="mt-2" />
                        </div>

                        <!-- District -->
                        <div class="mt-4">
                            <x-input-label for="district" :value="__('District')" />
                            <select id="district" name="district"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required>
                                <option value="">{{ __('Select District') }}</option>
                                @foreach ($districts as $district)
                                    <option value="{{ $district }}" {{ old('district') === $district ? 'selected' : '' }}>
                                        {{ $district }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('district')" class="mt-2" />
                        </div>

                        <!-- Phone -->
                        <div class="mt-4">
                            <x-input-label for="phone" :value="__('Phone Number')" />
                            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone"
                                          :value="old('phone')" required placeholder="01712345678" />
                            <p class="mt-1 text-xs text-gray-500">Bangladesh mobile number (e.g. 01712345678)</p>
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <!-- Last Donation Date (Optional) -->
                        <div class="mt-4">
                            <x-input-label for="last_donation_date" :value="__('Last Donation Date (Optional)')" />
                            <x-text-input id="last_donation_date" class="block mt-1 w-full" type="date"
                                          name="last_donation_date" :value="old('last_donation_date')"
                                          :max="date('Y-m-d')" />
                            <p class="mt-1 text-xs text-gray-500">If you have donated before, enter the date of your last donation.</p>
                            <x-input-error :messages="$errors->get('last_donation_date')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Save & Verify Phone') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
