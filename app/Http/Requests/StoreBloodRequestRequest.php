<?php

namespace App\Http\Requests;

use App\Helpers\BangladeshDistricts;
use App\Models\BloodRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class StoreBloodRequestRequest extends FormRequest
{
    /**
     * Public form — no auth required.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_name' => ['required', 'string', 'max:255'],
            'blood_group' => [
                'required',
                Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            ],
            'district' => [
                'required',
                'string',
                Rule::in(BangladeshDistricts::all()),
            ],
            'hospital' => ['required', 'string', 'max:255'],
            'urgency' => [
                'required',
                Rule::in(['normal', 'urgent', 'critical']),
            ],
            'requester_phone' => [
                'required',
                'string',
                'regex:/^01[3-9]\d{8}$/',
            ],
            'additional_notes' => ['nullable', 'string', 'max:1000'],
            'g-recaptcha-response' => [
                config('services.recaptcha.secret') ? 'required' : 'nullable',
                'string',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // ── Rate-limit: max 3 active requests per phone ──
            if (! $validator->errors()->has('requester_phone')) {
                $activeCount = BloodRequest::where('requester_phone', $this->requester_phone)
                    ->where('status', 'active')
                    ->count();

                if ($activeCount >= 3) {
                    $validator->errors()->add(
                        'requester_phone',
                        'You already have 3 active blood requests. Please wait for existing requests to expire or be fulfilled.'
                    );
                }
            }

            // ── reCAPTCHA v2 server-side verification ──
            if (! $validator->errors()->has('g-recaptcha-response') && config('services.recaptcha.secret')) {
                $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret'   => config('services.recaptcha.secret'),
                    'response' => $this->input('g-recaptcha-response'),
                    'remoteip' => $this->ip(),
                ]);

                if (! $response->json('success')) {
                    $validator->errors()->add(
                        'g-recaptcha-response',
                        'reCAPTCHA verification failed. Please try again.'
                    );
                }
            }
        });
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'requester_phone.regex'          => 'Please enter a valid Bangladesh mobile number (e.g. 01712345678).',
            'blood_group.in'                 => 'Please select a valid blood group.',
            'district.in'                    => 'Please select a valid Bangladesh district.',
            'urgency.in'                     => 'Please select a valid urgency level.',
            'g-recaptcha-response.required'  => 'Please complete the reCAPTCHA verification.',
        ];
    }
}
