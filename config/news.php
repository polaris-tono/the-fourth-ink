<?php

return [

    // NewsData.io settings. The key lives in .env (NEWSDATA_API_KEY) and is never committed.
    'key' => env('NEWSDATA_API_KEY'),

    'base_url' => 'https://newsdata.io/api/1/latest',

    // When the homepage loads and the stored headlines are older than this, refresh them.
    'auto_refresh' => env('NEWS_AUTO_REFRESH', true),
    'refresh_minutes' => 60,

    // Headlines older than this many days are deleted after each refresh.
    'keep_days' => 7,

    // The free plan allows at most 10 results per request.
    'page_size' => 10,

    // One request per category. The key is the category slug in our own database.
    // "campus" is not listed because campus news is written by our own editors.
    'feeds' => [
        'national' => ['country' => 'bd', 'language' => 'en'],
        'international' => ['category' => 'world', 'language' => 'en'],
        'technology' => ['category' => 'technology', 'language' => 'en'],
        'sports' => ['category' => 'sports', 'language' => 'en'],
        'business' => ['category' => 'business', 'language' => 'en'],
        'entertainment' => ['category' => 'entertainment', 'language' => 'en'],
    ],
];