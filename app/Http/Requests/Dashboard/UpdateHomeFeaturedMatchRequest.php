<?php

namespace App\Http\Requests\Dashboard;

use App\Models\HomeFeaturedMatch;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHomeFeaturedMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ticker_match_ids' => array_values(array_filter((array) $this->input('ticker_match_ids', []))),
            'is_active' => $this->boolean('is_active', true),
            'remove_banner' => $this->boolean('remove_banner'),
        ]);
    }

    public function rules(): array
    {
        return [
            'match_id' => ['nullable', 'string', 'exists:football_matches,match_id'],
            'ticker_match_ids' => ['array', 'max:' . HomeFeaturedMatch::TICKER_LIMIT],
            'ticker_match_ids.*' => ['string', 'distinct', 'exists:football_matches,match_id', 'different:match_id'],
            'is_active' => ['boolean'],
            'banner' => ['nullable', 'file', 'image', 'max:5120'],
            'remove_banner' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'ticker_match_ids.max' => 'You can select up to ' . HomeFeaturedMatch::TICKER_LIMIT . ' additional matches.',
            'ticker_match_ids.*.different' => 'The featured match cannot also be one of the additional matches.',
        ];
    }
}
