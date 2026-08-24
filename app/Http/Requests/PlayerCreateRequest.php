<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PlayerCreateRequest extends FormRequest
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
            // Required fields
            'first_name'  => ['required', 'string', 'max:255'],
            'last_name'   => ['required', 'string', 'max:255'],
            'position_id' => ['required', 'string', 'max:10', 'exists:positions,id'],
            'team_id'     => ['required', 'string', 'max:10', 'exists:teams,id'],

            // Optional but validated fields
            'espn_id'       => ['nullable', 'integer', 'unique:players,espn_id'],
            'pfr_id'        => ['nullable', 'string', 'unique:players,pfr_id'],
            'fp_id'         => ['nullable', 'string', 'unique:players,fp_id'],
            'full_name'     => ['nullable', 'string', 'max:255'],
            'jersey_number' => ['nullable', 'integer', 'min:0', 'max:99'],
            'height'        => ['nullable', 'string', 'max:255'],
            'weight'        => ['nullable', 'string', 'max:255'],
            'college'       => ['nullable', 'string', 'max:255'],
            'draft_year'    => ['nullable', 'string', 'max:255'],
            'draft_round'   => ['nullable', 'string', 'max:255'],
            'draft_pick'    => ['nullable', 'string', 'max:255'],
            'draft_team'    => ['nullable', 'string', 'max:255'],
            'birth_date'    => ['nullable', 'date'],
            'headshot'      => ['nullable', 'string', 'max:255', 'url'],
        ];
    }
}
