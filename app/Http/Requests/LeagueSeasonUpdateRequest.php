<?php

namespace App\Http\Requests;

use App\Models\LeagueSeason;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeagueSeasonUpdateRequest extends FormRequest
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
        $league = $this->route('league');
        $season = $this->route('season');

        return [
            'year' => [
                'integer',
                'min:2000',
                'max:' . (date('Y') + 1),
                Rule::unique('league_seasons')->where(function ($query) use ($league, $season) {
                    return $query->where('league_id', $league->id)
                        ->where('id', '!=', $season->id);
                }),
            ],
            'name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_completed' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'previous_season_id' => [
                'nullable',
                'exists:league_seasons,id',
                function ($attribute, $value, $fail) use ($league, $season) {
                    if ($value) {
                        if ($value == $season->id) {
                            $fail('A season cannot be its own previous season.');
                            return;
                        }
                        
                        $previousSeason = LeagueSeason::find($value);
                        if ($previousSeason && $previousSeason->league_id !== $league->id) {
                            $fail('The previous season must belong to the same league.');
                        }
                    }
                },
            ],
        ];
    }
}
