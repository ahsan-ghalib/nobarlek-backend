<?php

namespace App\Http\Requests;

use App\Enums\MatchEnum;
use App\Enums\SearchByEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'match' => ['required', Rule::in(MatchEnum::values())],
            'search_by' => ['required', Rule::in(SearchByEnum::values())],
            'keyword' => ['required', 'string', 'max:255'],
        ];
    }
}
