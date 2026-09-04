<?php

namespace App\Http\Requests;

use App\Models\LeagueMemberRoster;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PickStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('record', $this->route('draft')) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $draft = $this->route('draft');

        return [
            'player_id' => [
                'required',
                'integer',
                'exists:players,id',
                // A player can only be taken once.
                Rule::unique('draft_picks', 'player_id')->where('draft_id', $draft->id),
                // And a keeper was taken before the draft opened.
                Rule::notIn($this->keptPlayerIds()),
            ],
            // The team is the one the order puts on the clock, so it is only
            // sent when a pick is being recorded out of turn on purpose.
            'league_member_id' => [
                'sometimes',
                'integer',
                Rule::exists('league_members', 'id')->where('league_id', $draft->league_id),
            ],
        ];
    }

    /**
     * @return array<int, int>
     */
    private function keptPlayerIds(): array
    {
        $draft = $this->route('draft');

        return LeagueMemberRoster::whereIn('league_member_id', $draft->league->members->pluck('id'))
            ->where('season', $draft->league->season_id)
            ->where('week', 0)
            ->pluck('player_id')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'player_id.unique'        => 'That player has already been drafted.',
            'player_id.not_in'        => 'That player is being kept and was never in the draft.',
            'league_member_id.exists' => 'That team is not in this league.',
        ];
    }
}
