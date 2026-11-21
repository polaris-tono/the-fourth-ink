<?php

namespace App\Services;

use App\Exceptions\NewsApiException;
use App\Models\Category;
use App\Models\ExternalArticle;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class NewsApiService
{
    private const LAST_RUN_KEY = 'news:last-refresh';

    /**
     * Feeds that must contain local stories only. The news service tags wire stories
     * (press releases, regional agencies) with several countries, so a story is kept
     * in these feeds only when its single listed country is one of the accepted values.
     */
    private const LOCAL_ONLY_FEEDS = [
        'national' => ['bd', 'bangladesh'],
    ];

    public function isConfigured(): bool
    {
        return filled(config('news.key'));
    }

    /**
     * Refresh every configured feed (or only one category slug).
     * Returns one result row per category and never throws, so a failing
     * news service can never break a page.
     */
    public function refreshAll(?string $only = null): array
    {
        $results = [];

        if (! $this->isConfigured()) {
            return $results;
        }

        // Mark the attempt first, so even a failed run waits a full interval (protects the daily limit).
        Cache::put(self::LAST_RUN_KEY, now()->timestamp, now()->addDay());

        $stop = false;

        foreach (config('news.feeds') as $slug => $params) {
            if ($only !== null && $only !== $slug) {
                continue;
            }

            $category = Category::where('slug', $slug)->first();

            if (! $category) {
                $results[$slug] = ['status' => 'skipped', 'message' => 'No category with this slug'];
                continue;
            }

            if ($stop) {
                $results[$slug] = ['status' => 'skipped', 'message' => 'Skipped after an earlier fatal error'];
                continue;
            }

            try {
                $items = $this->fetch($params);
                $stats = $this->store($category, $items);

                $results[$slug] = [
                    'status' => 'ok',
                    'fetched' => count($items),
                    'saved' => $stats['saved'],
                    'message' => $stats['skipped'] > 0 ? "{$stats['skipped']} skipped (duplicate or not local)" : '',
                ];
            } catch (NewsApiException $e) {
                $results[$slug] = ['status' => 'error', 'message' => $e->getMessage()];
                Log::warning("News refresh failed for [{$slug}]: " . $e->getMessage());
                $stop = $e->fatal;
            } catch (Throwable $e) {
                $message = 'Unexpected error: ' . $this->scrub($e->getMessage());
                $results[$slug] = ['status' => 'error', 'message' => $message];
                Log::error("News refresh crashed for [{$slug}]: " . $message);
            }
        }

        try {
            $this->purgeOld();
        } catch (Throwable $e) {
            Log::error('News cleanup failed: ' . $this->scrub($e->getMessage()));
        }

        return $results;
    }

    /** Used by the homepage: refreshes only when the stored headlines are stale. Never throws. */
    public function refreshIfStale(): void
    {
        if (! config('news.auto_refresh') || ! $this->isConfigured()) {
            return;
        }

        $last = (int) Cache::get(self::LAST_RUN_KEY, 0);

        if (now()->timestamp - $last < config('news.refresh_minutes') * 60) {
            return;
        }

        // The lock makes sure only one visitor triggers the refresh at a time.
        $lock = Cache::lock('news-refresh', 120);

        if (! $lock->get()) {
            return;
        }

        try {
            $this->refreshAll();
        } catch (Throwable $e) {
            Log::error('News auto refresh failed: ' . $this->scrub($e->getMessage()));
        } finally {
            $lock->release();
        }
    }

    /** Delete headlines older than the retention period. */
    public function purgeOld(): int
    {
        $cutoff = now()->subDays((int) config('news.keep_days'));

        return ExternalArticle::query()
            ->where(function ($query) use ($cutoff) {
                $query->where('published_at', '<', $cutoff)
                    ->orWhere(fn ($q) => $q->whereNull('published_at')->where('fetched_at', '<', $cutoff));
            })
            ->delete();
    }

    /** One request to the news service. Returns the list of result items. */
    private function fetch(array $params): array
    {
        try {
            $response = Http::acceptJson()
                ->connectTimeout(4)
                ->timeout(8)
                ->get(config('news.base_url'), array_merge($params, [
                    'apikey' => config('news.key'),
                    'size' => config('news.page_size'),
                ]));
        } catch (ConnectionException $e) {
            throw new NewsApiException('Could not reach the news service: ' . $this->scrub($e->getMessage()), true);
        }

        if ($response->failed()) {
            $status = $response->status();
            $detail = data_get($response->json(), 'results.message');
            $detail = is_string($detail) ? $detail : "HTTP {$status}";

            // Bad key (401/403), rate limit (429) and server errors: stop sending more requests.
            $fatal = in_array($status, [401, 403, 429], true) || $status >= 500;

            throw new NewsApiException("News service answered {$status}: " . $this->scrub($detail), $fatal);
        }

        $body = $response->json();

        if (! is_array($body) || ($body['status'] ?? null) !== 'success') {
            throw new NewsApiException('Unexpected reply from the news service.');
        }

        $results = $body['results'] ?? [];

        return is_array($results) ? $results : [];
    }

    /**
     * Save the items. A story is never stored twice: not under the same address,
     * and not under the same headline at a different address (syndicated copies).
     *
     * @return array{saved:int, skipped:int}
     */
    private function store(Category $category, array $items): array
    {
        $saved = 0;
        $skipped = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = mb_substr(trim(strip_tags((string) ($item['title'] ?? ''))), 0, 500);
            $link = trim((string) ($item['link'] ?? ''));

            // Data from an outside service is untrusted: skip anything without a title or a real web link.
            if ($title === '' || ! preg_match('#^https?://#i', $link)) {
                $skipped++;
                continue;
            }

            // Keep the National feed for Bangladesh-only sources.
            if (! $this->isLocalEnough($category->slug, $item)) {
                $skipped++;
                continue;
            }

            $hash = hash('sha256', $link);

            $row = ExternalArticle::where('url_hash', $hash)->first()
                ?? ExternalArticle::where('title', $title)->first();

            // The same headline is already stored under another address: just keep that copy fresh.
            if ($row && $row->url_hash !== $hash) {
                $row->forceFill(['fetched_at' => now()])->save();
                $skipped++;
                continue;
            }

            $row ??= new ExternalArticle(['url_hash' => $hash]);

            // A story keeps the category it was first saved under.
            if (! $row->exists) {
                $row->category_id = $category->id;
            }

            $description = trim(strip_tags((string) ($item['description'] ?? '')));
            $image = trim((string) ($item['image_url'] ?? ''));
            $source = trim((string) ($item['source_name'] ?? $item['source_id'] ?? ''));

            $row->fill([
                'source_name' => $source !== '' ? mb_substr($source, 0, 150) : null,
                'title' => $title,
                'description' => $description !== '' ? Str::limit($description, 600) : null,
                'url' => $link,
                'image_url' => preg_match('#^https?://#i', $image) ? $image : null,
                'published_at' => $this->parseDate($item['pubDate'] ?? null),
                'fetched_at' => now(),
            ]);
            $row->save();

            $saved++;
        }

        return ['saved' => $saved, 'skipped' => $skipped];
    }

    /** True unless this feed is local-only and the story is not tagged with just that one country. */
    private function isLocalEnough(string $slug, array $item): bool
    {
        $accepted = self::LOCAL_ONLY_FEEDS[$slug] ?? null;

        if ($accepted === null) {
            return true;
        }

        $countries = $item['country'] ?? null;

        if (! is_array($countries) || count($countries) !== 1) {
            return false;
        }

        return in_array(strtolower(trim((string) reset($countries))), $accepted, true);
    }

    /** The service sends UTC times; convert them to the app's timezone. */
    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value, 'UTC')->timezone(config('app.timezone'));
        } catch (Throwable) {
            return null;
        }
    }

    /** Error messages can contain the request address, so the API key is always removed. */
    private function scrub(string $message): string
    {
        $key = (string) config('news.key');

        return $key === '' ? $message : str_replace($key, '[hidden]', $message);
    }
}