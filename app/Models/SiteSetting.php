<?php

namespace App\Models;

use App\Traits\GetImageAbsolutePathTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use GetImageAbsolutePathTrait;

    protected $fillable = [
        'site_name',
        'logo',
        'favicon',
        'contact_email',
        'contact_phone',
        'contact_address',
        'copyright_text',
        'social_heading',
        'social_links',
        'legal_links',
        'support_links',
        'terms_url',
        'privacy_policy_url',
        'terms_content',
        'privacy_policy_content',
        'app_section_title',
        'app_section_description',
        'app_store_url',
        'google_play_url',
        'lite_version_url',
        'lite_version_label',
        'privacy_settings_url',
        'privacy_settings_label',
        'footer_description',
        'news_page_heading',
        'news_page_description',
    ];

    protected $casts = [
        'social_links' => 'array',
        'legal_links' => 'array',
        'support_links' => 'array',
    ];

    protected $appends = [
        'logo_url',
        'favicon_url',
    ];

    public static function defaults(): array
    {
        return [
            'site_name' => 'Up Liga',
            'social_heading' => 'IKUTI KAMI',
            'social_links' => [
                ['platform' => 'facebook', 'label' => 'Facebook', 'url' => ''],
                ['platform' => 'x', 'label' => 'X', 'url' => ''],
                ['platform' => 'instagram', 'label' => 'Instagram', 'url' => ''],
                ['platform' => 'tiktok', 'label' => 'TikTok', 'url' => ''],
            ],
            'legal_links' => [
                ['label' => 'Ketentuan Penggunaan', 'url' => '', 'open_in_new_tab' => false],
                ['label' => 'Kebijakan Privasi', 'url' => '', 'open_in_new_tab' => false],
                ['label' => 'GDPR dan Jurnalisme', 'url' => '', 'open_in_new_tab' => false],
                ['label' => 'Impressum', 'url' => '', 'open_in_new_tab' => false],
                ['label' => 'Pasang Iklan', 'url' => '', 'open_in_new_tab' => false],
            ],
            'support_links' => [
                ['label' => 'Kontak', 'url' => '', 'open_in_new_tab' => false],
                ['label' => 'Telepon genggam', 'url' => '', 'open_in_new_tab' => false],
                ['label' => 'Skor Langsung', 'url' => '/', 'open_in_new_tab' => false],
                ['label' => 'Situs yang direkomendasikan', 'url' => '', 'open_in_new_tab' => false],
                ['label' => 'FAQ', 'url' => '', 'open_in_new_tab' => false],
            ],
            'app_section_title' => 'APLIKASI TELEPON GENGGAM',
            'app_section_description' => 'Aplikasi kami telah dioptimasi untuk telepon anda. Segera unduh gratis!',
            'lite_version_label' => 'Versi lite',
            'privacy_settings_label' => 'Atur Privasi',
            'copyright_text' => 'Up Liga',
            'terms_url' => '/terms',
            'privacy_policy_url' => '/privacy',
            'terms_content' => 'Ketentuan penggunaan belum diterbitkan.',
            'privacy_policy_content' => 'Kebijakan privasi belum diterbitkan.',
            'footer_description' => null,
            'news_page_heading' => 'Semua berita sepak bola',
            'news_page_description' => 'Sorotan utama, berita terkini, dan arsip lengkap — tampilan modern untuk menelusuri setiap liputan.',
        ];
    }

    public static function instance(): self
    {
        return static::query()->firstOrCreate([], self::defaults());
    }

    public function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->logo ? $this->getImageAbsolutePath($this->logo) : null,
        );
    }

    public function faviconUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->favicon ? $this->getImageAbsolutePath($this->favicon) : null,
        );
    }

    public function toPublicArray(): array
    {
        $data = $this->toArray();
        unset($data['logo'], $data['favicon']);

        return $data;
    }
}
