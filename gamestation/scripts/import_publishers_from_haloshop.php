<?php

declare(strict_types=1);

use Illuminate\Support\Str;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$categoryIds = [443, 445, 6019, 444]; // Game PS5, Game Switch, Game Switch 2, Game PS4
$productLinks = [];

$contextOptions = [
    'http' => [
        'timeout' => 5,
        'method' => 'GET',
    ],
    'https' => [
        'timeout' => 5,
        'method' => 'GET',
    ],
];
$streamContext = stream_context_create($contextOptions);

// Collect product URLs from max 2 pages per category
foreach ($categoryIds as $categoryId) {
    for ($page = 1; $page <= 2; $page++) {
        $url = "https://haloshop.vn/wp-json/wc/store/v1/products?category={$categoryId}&per_page=100&page={$page}";
        try {
            $json = @file_get_contents($url, false, $streamContext);
            if ($json === false) {
                echo "Skip page $page for category $categoryId (network timeout)\n";
                break;
            }

            $items = json_decode($json, true);
            if (!is_array($items) || count($items) === 0) {
                break;
            }

            foreach ($items as $item) {
                if (!empty($item['permalink'])) {
                    $productLinks[$item['permalink']] = true;
                }
            }
        } catch (\Throwable $e) {
            echo "Error fetching page $page for category $categoryId: {$e->getMessage()}\n";
            break;
        }
    }
}

echo "Total product links collected: " . count($productLinks) . PHP_EOL;

$publishers = [];
$count = 0;

foreach (array_keys($productLinks) as $productUrl) {
    $count++;
    try {
        $html = @file_get_contents($productUrl, false, $streamContext);
        if ($html === false) {
            continue;
        }

        $matches = [];
        $patterns = [
            '/Nhà sản xuất phát hành\s*<\/td>\s*<td[^>]*>\s*([^<]+)/u',
            '/Nhà phát hành\s*<\/td>\s*<td[^>]*>\s*([^<]+)/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $name = trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $name = preg_replace('/\s+/u', ' ', $name ?? '');

                if (!empty($name)) {
                    $publishers[$name] = true;
                }

                break;
            }
        }
        
        if ($count % 10 === 0) {
            echo "Processed $count product pages, found " . count($publishers) . " publishers so far\n";
        }
    } catch (\Throwable $e) {
        // Skip error pages silently
        continue;
    }
}

ksort($publishers);

$inserted = 0;
$existing = 0;

foreach (array_keys($publishers) as $name) {
    $found = \App\Models\Publisher::where('name', $name)->first();

    if ($found) {
        $existing++;
        continue;
    }

    $baseSlug = Str::slug($name);
    $slug = $baseSlug !== '' ? $baseSlug : Str::slug('publisher-' . $name);
    if ($slug === '') {
        $slug = 'publisher-' . Str::lower(Str::random(8));
    }

    $i = 1;
    $uniqueSlug = $slug;
    while (\App\Models\Publisher::where('slug', $uniqueSlug)->exists()) {
        $i++;
        $uniqueSlug = $slug . '-' . $i;
    }

    \App\Models\Publisher::create([
        'name' => $name,
        'slug' => $uniqueSlug,
    ]);

    $inserted++;
}

echo "HaloShop publishers found: " . count($publishers) . PHP_EOL;
echo "Inserted: {$inserted}" . PHP_EOL;
echo "Already existed: {$existing}" . PHP_EOL;

echo PHP_EOL . "Publisher list:" . PHP_EOL;
foreach (array_keys($publishers) as $name) {
    echo "- {$name}" . PHP_EOL;
}
