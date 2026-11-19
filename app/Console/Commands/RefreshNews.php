<?php

namespace App\Console\Commands;

use App\Services\NewsApiService;
use Illuminate\Console\Command;

class RefreshNews extends Command
{
    protected $signature = 'news:refresh {--category= : Refresh only this category slug, e.g. technology}';

    protected $description = 'Fetch the latest headlines from NewsData.io and store them in external_articles';

    public function handle(NewsApiService $news): int
    {
        if (! $news->isConfigured()) {
            $this->error('NEWSDATA_API_KEY is empty. Add it to your .env file, then run: php artisan config:clear');

            return self::FAILURE;
        }

        $only = $this->option('category') ?: null;
        $results = $news->refreshAll($only);

        if ($results === []) {
            $this->warn($only ? "No feed is configured for '{$only}'. See config/news.php." : 'No feeds are configured. See config/news.php.');

            return self::FAILURE;
        }

        $rows = [];
        $failed = false;

        foreach ($results as $slug => $result) {
            $failed = $failed || $result['status'] === 'error';
            $rows[] = [$slug, $result['status'], $result['fetched'] ?? '-', $result['saved'] ?? '-', $result['message'] ?? ''];
        }

        $this->table(['Category', 'Result', 'Fetched', 'Saved', 'Note'], $rows);

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}