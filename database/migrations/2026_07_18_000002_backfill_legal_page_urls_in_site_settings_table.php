<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('site_settings')
            ->whereNull('terms_url')
            ->orWhere('terms_url', '')
            ->update(['terms_url' => '/terms']);

        DB::table('site_settings')
            ->whereNull('privacy_policy_url')
            ->orWhere('privacy_policy_url', '')
            ->update(['privacy_policy_url' => '/privacy']);
    }

    public function down(): void
    {
        // Existing URL settings should not be erased on rollback.
    }
};
