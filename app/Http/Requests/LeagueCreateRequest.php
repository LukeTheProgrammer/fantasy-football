<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LeagueCreateRequest extends FormRequest
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
            'platform' => ['required', 'string', 'in:espn,cbs'],

            // ESPN
            'espn_league_id' => ['requiredIf:platform,espn', 'numeric'],
            'espn_s2'        => ['requiredIf:platform,espn', 'string'],
            'espn_swid'      => ['requiredIf:platform,espn', 'string'],
        ];
    }
}
