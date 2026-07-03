<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::updateOrCreate(['slug' => 'ps4'], [
            'name' => 'PlayStation 4',
            'description' => 'May PS4, dia game va phu kien chinh hang.',
            'image_url' => '/images/categories/ps4.jpg',
            'icon_url' => '/images/icons/iconps4ps5.png',
            'sort_order' => 1,
        ]);

        Category::updateOrCreate(['slug' => 'ps5'], [
            'name' => 'PlayStation 5',
            'description' => 'May PS5, dia game va phu kien cao cap.',
            'image_url' => '/images/categories/ps5.jpg',
            'icon_url' => '/images/icons/iconps4ps5.png',
            'sort_order' => 2,
        ]);

        Category::updateOrCreate(['slug' => 'switch'], [
            'name' => 'Nintendo Switch',
            'description' => 'May Nintendo Switch va phu kien di dong.',
            'image_url' => '/images/categories/switch.jpg',
            'icon_url' => '/images/icons/nintendo-switch.svg',
            'sort_order' => 3,
        ]);
    }
}
