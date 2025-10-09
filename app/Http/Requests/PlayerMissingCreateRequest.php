<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PlayerMissingCreateRequest extends FormRequest
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
            'source_class'    => ['required', 'string'],
            'source_data'     => ['required', 'array'],
            'unique_id_key'   => ['sometimes', 'nullable', 'string'],
            'unique_id_value' => ['sometimes', 'nullable', 'string'],
            'name'            => ['sometimes', 'nullable', 'string'],
            'position'        => ['sometimes', 'nullable', 'string'],
            'team'            => ['sometimes', 'nullable', 'string'],
        ];
    }
}
