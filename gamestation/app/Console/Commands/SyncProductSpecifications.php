<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SyncProductSpecifications extends Command
{
    protected $signature = 'products:sync-specifications {--dry-run : Show how many products would be updated without saving}';

    protected $description = 'Build detailed_description from structured product fields for existing products.';

    public function handle(): int
    {
        $products = Product::with('publisher')->get();
        $updated = 0;

        foreach ($products as $product) {
            $specLines = $this->buildSpecificationLines($product);

            if ($specLines->isEmpty()) {
                continue;
            }

            $newDescription = $specLines->implode(PHP_EOL);

            if ($product->detailed_description === $newDescription) {
                continue;
            }

            $updated++;

            if (! $this->option('dry-run')) {
                $product->forceFill([
                    'detailed_description' => $newDescription,
                ])->save();
            }
        }

        $this->info(sprintf(
            '%s %d product(s) %s.',
            $this->option('dry-run') ? 'Would update' : 'Updated',
            $updated,
            $this->option('dry-run') ? 'based on structured fields' : 'with specification rows'
        ));

        return self::SUCCESS;
    }

    private function buildSpecificationLines(Product $product): Collection
    {
        $lines = collect();

        $this->addLine($lines, 'Thể loại', $product->genre);
        $this->addLine($lines, 'Hệ máy', $this->formatPlatform($product->platform));
        $this->addLine($lines, 'ESRB', $this->formatEsrb($product->esrb));
        $this->addLine($lines, 'Ngày phát hành', $this->formatDate($product->release_date));
        $this->addLine($lines, 'Số người chơi', $product->players);
        $this->addLine($lines, 'Nhà sản xuất & phát hành', $product->publisher?->name ?? $product->publisher);

        if (is_array($product->specs) && ! empty($product->specs)) {
            foreach ($product->specs as $label => $value) {
                $this->addLine($lines, (string) $label, is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE));
            }
        }

        return $lines->unique()->values();
    }

    private function addLine(Collection $lines, string $label, mixed $value): void
    {
        $value = is_string($value) ? trim($value) : $value;

        if ($value === null || $value === '' || $value === []) {
            return;
        }

        $lines->push($label.': '.$value);
    }

    private function formatPlatform(?string $platform): ?string
    {
        if (! $platform) {
            return null;
        }

        return match (strtolower($platform)) {
            'ps4' => 'PS4',
            'ps5' => 'PS5',
            'switch' => 'Switch',
            'xbox' => 'Xbox',
            default => strtoupper($platform),
        };
    }

    private function formatEsrb(?string $esrb): ?string
    {
        if (! $esrb) {
            return null;
        }

        return match ($esrb) {
            'EC' => 'Early Childhood',
            'E' => 'Everyone',
            'E10+' => 'Everyone 10+',
            'T' => 'Teen',
            'M' => 'Mature',
            'AO' => 'Adults Only',
            default => $esrb,
        };
    }

    private function formatDate(mixed $releaseDate): ?string
    {
        if (! $releaseDate) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($releaseDate)->format('d/m/Y');
        } catch (\Throwable) {
            return (string) $releaseDate;
        }
    }
}
