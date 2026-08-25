<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFooterSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['social_links', 'legal_links', 'support_links'] as $field) {
            $value = $this->input($field);
            if (is_string($value) && $value !== '') {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->merge([$field => $decoded]);
                }
            }
        }

        if ($this->has('open_in_new_tab')) {
            $this->merge(['open_in_new_tab' => filter_var($this->input('open_in_new_tab'), FILTER_VALIDATE_BOOLEAN)]);
        }
    }

    public function rules(): array
    {
        return [
            'site_name' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'favicon' => ['nullable', 'image', 'max:2048'],
            'contact_email' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'contact_address' => ['nullable', 'string', 'max:2000'],
            'copyright_text' => ['nullable', 'string', 'max:255'],
            'social_heading' => ['nullable', 'string', 'max:255'],
            'social_links' => ['nullable', 'array'],
            'social_links.*.platform' => ['required_with:social_links', 'string', 'max:50'],
            'social_links.*.label' => ['required_with:social_links', 'string', 'max:255'],
            'social_links.*.url' => ['nullable', 'string', 'max:2048'],
            'legal_links' => ['nullable', 'array'],
            'legal_links.*.label' => ['required_with:legal_links', 'string', 'max:255'],
            'legal_links.*.url' => ['nullable', 'string', 'max:2048'],
            'legal_links.*.open_in_new_tab' => ['nullable', 'boolean'],
            'support_links' => ['nullable', 'array'],
            'support_links.*.label' => ['required_with:support_links', 'string', 'max:255'],
            'support_links.*.url' => ['nullable', 'string', 'max:2048'],
            'support_links.*.open_in_new_tab' => ['nullable', 'boolean'],
            'terms_url' => ['nullable', 'string', 'max:2048'],
            'privacy_policy_url' => ['nullable', 'string', 'max:2048'],
            'terms_content' => ['nullable', 'string', 'max:100000'],
            'privacy_policy_content' => ['nullable', 'string', 'max:100000'],
            'app_section_title' => ['nullable', 'string', 'max:255'],
            'app_section_description' => ['nullable', 'string', 'max:2000'],
            'app_store_url' => ['nullable', 'string', 'max:2048'],
            'google_play_url' => ['nullable', 'string', 'max:2048'],
            'lite_version_url' => ['nullable', 'string', 'max:2048'],
            'lite_version_label' => ['nullable', 'string', 'max:255'],
            'privacy_settings_url' => ['nullable', 'string', 'max:2048'],
            'privacy_settings_label' => ['nullable', 'string', 'max:255'],
            'footer_description' => ['nullable', 'string', 'max:5000'],
            'news_page_heading' => ['nullable', 'string', 'max:255'],
            'news_page_description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
