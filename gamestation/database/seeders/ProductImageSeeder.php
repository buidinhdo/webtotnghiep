<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;

class ProductImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Thêm ảnh cho các sản phẩm đã tồn tại
        $products = Product::limit(10)->get();

        foreach ($products as $product) {
            // Tạo ảnh nếu chưa có
            if ($product->images()->count() === 0) {
                // Ảnh placeholder từ picsum hoặc placeholder.com
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => 'images/products/1772872560_nintendo-switch-oled-white-joy-con-00-700x700-1 (1).jpg',
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);
            }
        }

        $this->command->info('Product images seeder completed!');
    }
}
