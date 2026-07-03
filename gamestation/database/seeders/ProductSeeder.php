<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');

        $images = collect(File::files(public_path('images/products')))
            ->filter(fn ($file) => $file->isFile())
            ->sortBy(fn ($file) => $file->getFilename())
            ->map(fn ($file) => '/images/products/'.$file->getFilename())
            ->values();

        if ($images->isEmpty()) {
            return;
        }

        Schema::disableForeignKeyConstraints();
        ProductImage::truncate();
        Product::truncate();
        Schema::enableForeignKeyConstraints();

        $reservedSlugs = [];
        $reservedSkus = [];

        foreach ($images as $index => $image) {
            $fileName = pathinfo($image, PATHINFO_FILENAME);
            $cleanName = preg_replace('/^\d+[_-]/', '', $fileName);
            $tokens = preg_split('/[\s_-]+/', $cleanName ?? '');
            $tokens = array_values(array_filter($tokens, function ($token) {
                return !preg_match('/^\d+x\d+h?$/i', $token)
                    && !preg_match('/^\d{2,4}x\d{2,4}h?$/i', $token)
                    && !preg_match('/^\d+$/', $token);
            }));
            $tokens = array_map(function ($token) {
                $lower = Str::lower($token);

                return match (true) {
                    $lower === 'ps5' => 'PS5',
                    $lower === 'ps4' => 'PS4',
                    $lower === 'ps' => 'PS',
                    $lower === 'sw' => 'Switch',
                    $lower === 'switch' => 'Switch',
                    $lower === 'nintendo' => 'Nintendo',
                    $lower === 'xbox' => 'Xbox',
                    $lower === 'xbox360' => 'Xbox 360',
                    $lower === 'xboxone' => 'Xbox One',
                    default => Str::title($token),
                };
            }, $tokens);
            $name = trim(implode(' ', $tokens));
            if ($name === '') {
                $name = 'San pham GameStation';
            }

            $slugBase = Str::slug($name);
            $slug = $slugBase ?: 'san-pham';
            $suffix = 2;
            while (in_array($slug, $reservedSlugs, true)) {
                $slug = $slugBase.'-'.$suffix;
                $suffix++;
            }
            $reservedSlugs[] = $slug;

            $lower = Str::lower($fileName);
            $platform = 'ps5';
            // GTA V detection
            if (Str::contains($lower, 'grand') && Str::contains($lower, 'theft') && Str::contains($lower, 'auto')) {
                $platform = 'ps4';
            } elseif (Str::contains($lower, ['gta-v', 'gta_v', 'gtav'])) {
                $platform = 'ps4';
            } elseif (Str::contains($lower, ['call-of-duty', 'black-ops', 'cod'])) {
                $platform = 'ps4';
            } elseif (Str::contains($lower, ['ps4', 'playstation-4', 'playstation4'])) {
                $platform = 'ps4';
            } elseif (Str::contains($lower, ['ps5', 'playstation-5', 'playstation5'])) {
                $platform = 'ps5';
            } elseif (Str::contains($lower, ['switch', 'sw', 'nintendo'])) {
                $platform = 'switch';
            }

            $categoryId = $categories[$platform] ?? $categories->first();
            if (!$categoryId) {
                continue;
            }

            $sku = strtoupper($platform).'-AUTO-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            $skuSuffix = 2;
            while (in_array($sku, $reservedSkus, true)) {
                $sku = strtoupper($platform).'-AUTO-'.str_pad((string) ($index + $skuSuffix), 3, '0', STR_PAD_LEFT);
                $skuSuffix++;
            }
            $reservedSkus[] = $sku;

            $price = match ($platform) {
                'ps4' => 990000,
                'switch' => 1290000,
                default => 1490000,
            };

            $product = Product::create([
                'category_id' => $categoryId,
                'name' => $name,
                'slug' => $slug,
                'platform' => $platform,
                'sku' => $sku,
                'price' => $price,
                'stock' => 20,
                'description' => 'Sản phẩm mới cập nhật từ bộ sưu tập GameStation.',
                'specs' => [
                    'Edition' => '2026',
                ],
                'featured' => $index < 12,
                'is_active' => true,
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $image,
                'is_primary' => true,
                'sort_order' => 1,
            ]);
        }
    }
}
