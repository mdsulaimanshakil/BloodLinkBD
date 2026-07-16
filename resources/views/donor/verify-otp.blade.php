<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Verify Your Phone Number') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Status messages --}}
                    @if (session('status'))
                        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-md text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{-- Debug OTP display (local environment only) --}}
                    @if (session('otp_debug'))
                        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-md text-sm">
                            <strong>Debug OTP:</strong> {{ session('otp_debug') }}
                            <br><span class="text-xs">(Only shown in local environment)</span>
                        </div>
                    @endif

                    <p class="mb-6 text-sm text-gray-600">
                        We've sent a 6-digit verification code to your email.
                        Please enter it below to verify your phone number
                        <strong>{{ $phone }}</strong>.
                    </p>

                    <form method="POST" action="{{ route('donor.otp.verify') }}">
                        @csrf

                        <!-- OTP Code -->
                        <div>
                            <x-input-label for="code" :value="__('Verification Code')" />
                            <x-text-input id="code" class="block mt-1 w-full text-center text-2xl tracking-widest"
                                          type="text" name="code" maxlength="6" required autofocus
                                          placeholder="000000" autocomplete="one-time-code"
                                          inputmode="numeric" pattern="[0-9]{6}" />
                            <x-input-error :messages="$errors->get('code')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Verify') }}
                            </x-primary-button>
                        </div>
                    </form>

                    {{-- Separate resend form (since nested forms are invalid HTML) --}}
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <form method="POST" action="{{ route('donor.otp.resend') }}">
                            @csrf
                            <p class="text-sm text-gray-500">
                                Didn't receive the code?
                                <button type="submit"
                                        class="text-indigo-600 hover:text-indigo-500 underline focus:outline-none">
                                    Resend verification code
                                </button>
                            </p>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
