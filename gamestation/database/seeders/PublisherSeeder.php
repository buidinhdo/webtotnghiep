<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publishers = [
            'Activision',
            'All in! Games',
            'Aspyr',
            'Bandai Namco',
            'Capcom',
            'Electronic Arts',
            'Koei',
            'Kojima Productions / Konami',
            'Konami',
            'Microsoft',
            'Midway Games',
            'Mindscape',
            'Nintendo',
            'Red Art Games',
            'Rockstar Games',
            'Sega',
            'Silver Lining Interactive',
            'Sony Computer Entertainment',
            'Sony Interactive Entertainment',
            'Spike Chunsoft',
            'Square Enix',
            'THQ',
            'Ubisoft',
            'Warner Bros. Interactive Entertainment',
        ];

        foreach ($publishers as $name) {
            Publisher::firstOrCreate(
                ['name' => $name],
                ['slug' => $this->uniqueSlug($name)]
            );
        }
    }

    private function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug !== '' ? $baseSlug : 'publisher';
        $index = 2;

        while (Publisher::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $index;
            $index++;
        }

        return $slug;
    }
}
