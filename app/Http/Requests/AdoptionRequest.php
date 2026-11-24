<?php

namespace App\Http\Requests;

use App\Models\TreeAdoption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdoptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        foreach (['guardian_name', 'guardian_email', 'guardian_phone'] as $key) {
            if (is_string($this->input($key))) {
                $data[$key] = trim(strip_tags($this->input($key)));
            }
        }$this->merge($data);
    }

    public function rules(): array
    {
        return ['guardian_name' => 'required|string|max:160', 'guardian_email' => 'required|email|max:160', 'guardian_phone' => 'nullable|string|max:50',
            'adoption_type' => ['required', Rule::in(array_keys(TreeAdoption::TYPES))], 'public_display_preference' => ['required', Rule::in(array_keys(TreeAdoption::PREFERENCES))],
            'privacy_consent' => 'accepted', 'website' => 'prohibited', 'submission_token' => 'required|uuid'];
    }
}
