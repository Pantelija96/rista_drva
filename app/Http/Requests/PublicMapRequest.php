<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicMapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', Rule::in(['school', 'tree', 'adopted', 'group'])],
            'city' => ['nullable', 'string', 'max:160'],
            'school_id' => ['nullable', 'integer', 'min:1'],
            'graduation_year' => ['nullable', 'integer', 'between:1900,2200'],
            'species_id' => ['nullable', 'integer', 'min:1'],
            'adoption_status' => ['nullable', Rule::in(['unavailable', 'available', 'adopted'])],
            'search' => ['nullable', 'string', 'max:160'],
        ];
    }
}
