<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuctionPickUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('record', $this->route('draft')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The player is not editable: a sale recorded against the wrong player is
     * undone rather than corrected, since the board tracks him as gone.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $draft = $this->route('draft');

        return [
            'league_member_id' => [
                'required',
                'integer',
                Rule::exists('league_members', 'id')->where('league_id', $draft->league_id),
            ],
            'amount' => ['required', 'integer', 'min:1', 'max:' . (int) $draft->auction_budget],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'league_member_id.exists' => 'That team is not in this league.',
            'amount.max'              => 'A bid cannot exceed the auction budget.',
        ];
    }
}
