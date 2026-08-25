<?php

namespace App\Http\Requests;

use App\Enums\FavoriteEnum;
use App\Enums\SearchByEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFavoriteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return !is_null(auth()->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'favoriteable_type' => ['required', Rule::in(FavoriteEnum::values())],
            'favoriteable_id' => ['required'],
        ];
    }
}
