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
            'platform'    => ['required', 'string', 'in:espn,cbs'],
            'credentials' => ['required', 'array'],

            // Credentials are stored as one json object, so each platform
            // validates only the keys it authenticates with.
            'credentials.leagueId' => ['required', 'numeric'],
            'credentials.s2'       => ['required_if:platform,espn', 'string'],
            'credentials.swid'     => ['required_if:platform,espn', 'string'],
            'credentials.token'    => ['required_if:platform,cbs', 'string'],
        ];
    }
}
