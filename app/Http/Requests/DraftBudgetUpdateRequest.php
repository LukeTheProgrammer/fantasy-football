<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DraftBudgetUpdateRequest extends FormRequest
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
     * A plan may exceed the budget: planning past what you have is a mistake
     * worth seeing on screen rather than one worth refusing to save.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'allocations'   => ['present', 'array'],
            'allocations.*' => ['nullable', 'integer', 'min:0', 'max:' . (int) $this->route('draft')->auction_budget],
        ];
    }

    /**
     * Empty slots are stored as absent rather than as zero, so a plan only
     * holds the numbers actually written.
     *
     * @return array<string, int>
     */
    public function allocations(): array
    {
        return collect($this->validated('allocations'))
            ->filter(fn ($amount) => $amount !== null && $amount !== '')
            ->map(fn ($amount) => (int) $amount)
            ->filter(fn (int $amount) => $amount > 0)
            ->all();
    }
}
