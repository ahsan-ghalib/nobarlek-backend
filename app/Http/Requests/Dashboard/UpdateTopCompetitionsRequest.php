<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTopCompetitionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'competition_ids' => ['present', 'array', 'max:50'],
            'competition_ids.*' => ['string', 'distinct', 'exists:competitions,competition_id'],
        ];
    }
}
