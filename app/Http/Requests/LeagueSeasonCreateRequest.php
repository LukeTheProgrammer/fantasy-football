<?php

namespace App\Http\Requests;

use App\Models\LeagueSeason;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeagueSeasonCreateRequest extends FormRequest
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

        return [
            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:' . (date('Y') + 1),
                Rule::unique('league_seasons')->where(function ($query) use ($league) {
                    return $query->where('league_id', $league->id);
                }),
            ],
            'name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'previous_season_id' => [
                'nullable',
                'exists:league_seasons,id',
                function ($attribute, $value, $fail) use ($league) {
                    if ($value) {
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
