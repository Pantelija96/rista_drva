<?php

namespace App\Http\Requests;

use App\Support\DomainRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $clean = [];
        foreach (['school_name', 'city', 'municipality', 'contact_person', 'contact_role', 'email', 'phone', 'message'] as $field) {
            $value = $this->input($field);
            if (is_string($value)) {
                $clean[$field] = trim(strip_tags($value));
            }
        }
        if (isset($clean['email'])) {
            $clean['email'] = mb_strtolower($clean['email']);
        }
        $this->merge($clean);
    }

    public function rules(): array
    {
        return [
            'school_name' => 'required|string|max:160', 'city' => 'required|string|max:160', 'municipality' => 'nullable|string|max:160',
            'contact_person' => 'required|string|max:160', 'contact_role' => 'nullable|string|max:160', 'email' => 'required|email|max:160',
            'phone' => 'nullable|string|max:50', 'desired_planting_model' => ['required', Rule::in([...array_keys(DomainRules::PLANTING), 'undecided'])],
            'estimated_graduate_count' => 'nullable|integer|min:1|max:100000', 'message' => 'nullable|string|max:3000',
            'privacy_consent' => 'accepted', 'website' => 'prohibited', 'submission_token' => 'required|uuid',
        ];
    }

    public function messages(): array
    {
        return ['required' => __('public.this_field_is_required'), 'email' => __('public.enter_a_valid_email_address'), 'accepted' => __('public.consent_is_required'),
            'max' => __('public.the_value_is_too_long_or_too_large'), 'integer' => __('public.enter_a_whole_number'), 'in' => __('public.select_one_of_the_available_options'), 'prohibited' => __('public.the_application_cannot_be_submitted')];
    }
}
