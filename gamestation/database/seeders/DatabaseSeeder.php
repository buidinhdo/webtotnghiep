<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@gamestation.test'],
            [
                'name' => 'GameStation Demo',
                'password' => Hash::make('password'),
                'phone' => '0900000000',
                'address' => '123 Game Street, Ho Chi Minh City',
            ]
        );

        Cart::firstOrCreate(['user_id' => $user->id]);

        $this->call([
            CategorySeeder::class,
            PublisherSeeder::class,
            ProductSeeder::class,
            CouponSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
