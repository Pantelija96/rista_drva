<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        foreach (['name', 'email', 'phone', 'message', 'organization'] as $key) {
            if (is_string($this->input($key))) {
                $data[$key] = trim(strip_tags($this->input($key)));
            }
        }$this->merge($data);
    }

    public function rules(): array
    {
        $topics = $this->route('kind') === 'support' ? ['tree_donation', 'event_organization', 'financial_support', 'equipment_donation', 'volunteering', 'other'] : ['general', 'school', 'volunteering', 'tree_donation', 'financial_support', 'partnership', 'other'];

        return ['name' => 'required|string|max:160', 'email' => 'required|email|max:160', 'phone' => 'nullable|string|max:50', 'organization' => 'nullable|string|max:160', 'topic' => ['required', Rule::in($topics)], 'message' => 'required|string|max:3000', 'privacy_consent' => 'accepted', 'website' => 'prohibited', 'submission_token' => 'required|uuid'];
    }
}
