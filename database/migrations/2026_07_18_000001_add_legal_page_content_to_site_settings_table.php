<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->longText('terms_content')->nullable()->after('privacy_policy_url');
            $table->longText('privacy_policy_content')->nullable()->after('terms_content');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['terms_content', 'privacy_policy_content']);
        });
    }
};
