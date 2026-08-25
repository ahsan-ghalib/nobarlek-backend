<?php

namespace App\Http\Requests;

use App\Enums\SearchByEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNewsRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image' => ['required', 'image', 'max:2096'],
            'newsable_id' => [
                'nullable',
                'required_with:newsable_type',
                match ($this->newsable_type) {
                    SearchByEnum::Teams->value => Rule::exists('teams', 'id'),
                    SearchByEnum::Leagues->value => Rule::exists('competitions', 'id'),
                    SearchByEnum::Players->value => Rule::exists('players', 'id'),
                    default => '',
                }
            ],
            'newsable_type' => ['nullable', 'required_with:newsable_id', Rule::in(SearchByEnum::values())]
        ];
    }
}
