<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('contact_address')->nullable();
            $table->string('copyright_text')->nullable();
            $table->string('social_heading')->default('IKUTI KAMI');
            $table->json('social_links')->nullable();
            $table->json('legal_links')->nullable();
            $table->json('support_links')->nullable();
            $table->string('terms_url')->nullable();
            $table->string('privacy_policy_url')->nullable();
            $table->string('app_section_title')->nullable();
            $table->text('app_section_description')->nullable();
            $table->string('app_store_url')->nullable();
            $table->string('google_play_url')->nullable();
            $table->string('lite_version_url')->nullable();
            $table->string('lite_version_label')->nullable();
            $table->string('privacy_settings_url')->nullable();
            $table->string('privacy_settings_label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
