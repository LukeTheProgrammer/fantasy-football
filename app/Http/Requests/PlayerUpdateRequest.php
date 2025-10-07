<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PlayerUpdateRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name'     => ['sometimes', 'string'],
            'last_name'      => ['sometimes', 'string'],
            'full_name'      => ['sometimes', 'string'],
            'height'         => ['sometimes', 'string', 'nullable'],
            'weight'         => ['sometimes', 'string', 'nullable'],
            'college'        => ['sometimes', 'string', 'nullable'],
            'draft_year'     => ['sometimes', 'string', 'nullable'],
            'jersey_number'  => ['sometimes', 'integer', 'min:0', 'max:99', 'nullable'],
            'aliases'        => ['sometimes', 'array'],
            'aliases.*.name' => ['sometimes', 'string'],
        ];
    }
}
