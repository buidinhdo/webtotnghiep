<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            return;
        }

        $messages = [
            [
                'title' => 'Chao mung den GameStation',
                'body' => 'Cam on ban da tham gia GameStation! Kham pha deal moi nhat ngay hom nay.',
            ],
            [
                'title' => 'Ma giam gia GAMER10',
                'body' => 'Nhap GAMER10 de giam 10% cho don hang tu 1.000.000d.',
            ],
        ];

        foreach ($messages as $message) {
            UserNotification::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'title' => $message['title'],
                ],
                [
                    'body' => $message['body'],
                    'read_at' => null,
                ]
            );
        }
    }
}
