<?php

namespace App\Http\Requests;

use App\Enums\OddsTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvokeCompetitionRequest extends FormRequest
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
            'is_live' => ['nullable', 'bool'],
            'is_finished' => ['nullable', 'bool'],
            'is_scheduled' => ['nullable', 'bool'],
            'has_stream' => ['nullable', 'bool'],
            'odd_type' => ['required', Rule::in(OddsTypeEnum::values())],
            'date' => ['nullable', 'required_if:is_live,false', 'date:Y-m-d'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'tz' => ['nullable', 'string', 'max:64'],
        ];
    }
}
