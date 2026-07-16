<?php

namespace App\Http\Requests;

use App\Helpers\BangladeshDistricts;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteDonorProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'blood_group' => [
                'required',
                Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            ],
            'district' => [
                'required',
                'string',
                Rule::in(BangladeshDistricts::all()),
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^01[3-9]\d{8}$/', // Bangladesh mobile number format
                Rule::unique('donor_profiles', 'phone')
                    ->ignore($this->user()?->donorProfile?->id),
            ],
            'last_donation_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid Bangladesh mobile number (e.g. 01712345678).',
            'blood_group.in' => 'Please select a valid blood group.',
            'district.in' => 'Please select a valid Bangladesh district.',
            'last_donation_date.before_or_equal' => 'Last donation date cannot be in the future.',
        ];
    }
}
