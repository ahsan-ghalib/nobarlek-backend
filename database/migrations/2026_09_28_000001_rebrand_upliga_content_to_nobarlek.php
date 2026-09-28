<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Content saved from the dashboard still carried the old "UpLiga" brand
     * (site name, footer, legal pages, SEO, news). Rewrite it to Nobarlek.
     * Irreversible: the old wording is not kept.
     */
    public function up(): void
    {
        $siteSettings = DB::table('site_settings')->first();
        if ($siteSettings) {
            $updates = [];
            foreach ((array) $siteSettings as $column => $value) {
                if ($column === 'id' || ! is_string($value)) {
                    continue;
                }
                if (in_array($column, ['site_name', 'copyright_text'], true) && preg_match('/^\s*up\s?liga\s*$/i', $value)) {
                    $updates[$column] = 'NOBARLEK';
                } elseif (preg_match('/up\s?liga/i', $value)) {
                    $updates[$column] = $this->rebrand($value);
                }
            }
            if ($updates !== []) {
                DB::table('site_settings')->where('id', $siteSettings->id)->update($updates);
            }
        }

        foreach (['home_seos', 'news', 'blogs'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach (Schema::getColumnListing($table) as $column) {
                DB::table($table)
                    ->where($column, 'like', '%liga%')
                    ->select(['id', $column])
                    ->orderBy('id')
                    ->chunkById(200, function ($rows) use ($table, $column) {
                        foreach ($rows as $row) {
                            $value = $row->{$column};
                            if (is_string($value) && preg_match('/up\s?liga/i', $value)) {
                                DB::table($table)->where('id', $row->id)->update([$column => $this->rebrand($value)]);
                            }
                        }
                    });
            }
        }

        $this->rebrandEntitySeo();
    }

    /** SEO fields edited per competition / team / player / match in the dashboard. */
    private function rebrandEntitySeo(): void
    {
        foreach (['competitions', 'teams', 'players', 'football_matches'] as $table) {
            foreach (['meta_title', 'meta_description', 'meta_keywords'] as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }
                DB::table($table)
                    ->where(fn ($q) => $q->where($column, 'like', '%upliga%')->orWhere($column, 'like', '%up liga%'))
                    ->select(['id', $column])
                    ->orderBy('id')
                    ->chunkById(200, function ($rows) use ($table, $column) {
                        foreach ($rows as $row) {
                            DB::table($table)->where('id', $row->id)->update([$column => $this->rebrand($row->{$column})]);
                        }
                    });
            }
        }
    }

    public function down(): void
    {
        // Content rewrite; nothing to restore.
    }

    private function rebrand(string $text): string
    {
        $text = preg_replace('/up\s?liga\.com/i', 'Nobarlek', $text);

        return preg_replace('/up\s?liga/i', 'Nobarlek', $text);
    }
};
