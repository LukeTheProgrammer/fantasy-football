<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DraftPickCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $league = $this->route('league');
        return $this->user()->can('update', $league);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $draft = $this->route('draft');

        return [
            'player_id' => [
                'required',
                'exists:players,id',
                Rule::unique('draft_picks', 'player_id')->where(function ($query) use ($draft) {
                    return $query->where('draft_id', $draft->id);
                }),
            ],
            'amount' => ['required_if:draft_type,auction', 'nullable', 'numeric', 'min:1'],
            'is_keeper' => ['boolean'],
            'previous_year_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
