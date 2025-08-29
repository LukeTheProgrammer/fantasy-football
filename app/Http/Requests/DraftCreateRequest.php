<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DraftCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('league'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'draft_date' => ['required', 'date', 'after:now'],
            'draft_type' => ['required', Rule::in(['snake', 'auction'])],
            'auction_budget' => ['required_if:draft_type,auction', 'nullable', 'integer', 'min:1', 'max:1000'],
            'time_per_pick' => ['integer', 'min:10', 'max:600'],
        ];
    }
}
