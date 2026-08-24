<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LeagueUpdateRequest extends FormRequest
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
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'team_count'  => ['sometimes', 'integer', 'min:2', 'max:32'],
            'is_public'   => ['sometimes', 'boolean'],
            'draft_type'  => ['sometimes', 'string', 'in:snake,auction'],
            'draft_date'  => ['sometimes', 'nullable', 'date'],
            'platform'    => ['sometimes', 'string', 'in:espn,cbs'],

            // Credentials are one json object, replaced wholesale rather than
            // merged, so a rotated cookie cannot leave a stale one behind.
            'credentials'          => ['sometimes', 'array'],
            'credentials.leagueId' => ['required_with:credentials', 'numeric'],
            'credentials.s2'       => ['sometimes', 'string'],
            'credentials.swid'     => ['sometimes', 'string'],
            'credentials.token'    => ['sometimes', 'string'],

            // League settings validation
            'settings.roster_positions' => ['sometimes', 'array'],
            'settings.roster_size'      => ['sometimes', 'integer', 'min:1'],
            'settings.starters_count'   => ['sometimes', 'integer', 'min:1'],
            'settings.bench_count'      => ['sometimes', 'integer', 'min:0'],
            'settings.ir_spots'         => ['sometimes', 'integer', 'min:0'],

            // Scoring settings
            'settings.passing_points_per_yard'        => ['sometimes', 'numeric'],
            'settings.passing_td_points'              => ['sometimes', 'numeric'],
            'settings.interception_points'            => ['sometimes', 'numeric'],
            'settings.rushing_points_per_yard'        => ['sometimes', 'numeric'],
            'settings.rushing_td_points'              => ['sometimes', 'numeric'],
            'settings.receiving_points_per_yard'      => ['sometimes', 'numeric'],
            'settings.receiving_td_points'            => ['sometimes', 'numeric'],
            'settings.reception_points'               => ['sometimes', 'numeric'],
            'settings.fumble_lost_points'             => ['sometimes', 'numeric'],
            'settings.two_point_conversion_points'    => ['sometimes', 'numeric'],
            'settings.field_goal_0_39_points'         => ['sometimes', 'numeric'],
            'settings.field_goal_40_49_points'        => ['sometimes', 'numeric'],
            'settings.field_goal_50_plus_points'      => ['sometimes', 'numeric'],
            'settings.extra_point_points'             => ['sometimes', 'numeric'],
            'settings.defense_sack_points'            => ['sometimes', 'numeric'],
            'settings.defense_interception_points'    => ['sometimes', 'numeric'],
            'settings.defense_fumble_recovery_points' => ['sometimes', 'numeric'],
            'settings.defense_td_points'              => ['sometimes', 'numeric'],
            'settings.defense_safety_points'          => ['sometimes', 'numeric'],
            'settings.defense_points_allowed_tiers'   => ['sometimes', 'array'],
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
            'team_count.required'                => 'The number of teams is required.',
            'team_count.min'                     => 'The league must have at least 2 teams.',
            'team_count.max'                     => 'The league cannot have more than 32 teams.',
            'settings.roster_positions.required' => 'Roster positions are required.',
            'settings.roster_positions.array'    => 'Roster positions must be an array.',
            'settings.starters_count.min'        => 'At least one starter is required.',
            'settings.roster_size.min'           => 'Roster size must be at least 1.',
        ];
    }
}
