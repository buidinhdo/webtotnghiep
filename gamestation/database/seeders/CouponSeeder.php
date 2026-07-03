<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'GAMER10',
                'type' => 'percent',
                'value' => 10,
                'min_order' => 1000000,
                'is_active' => true,
            ],
            [
                'code' => 'SHIP50',
                'type' => 'fixed',
                'value' => 50000,
                'min_order' => 500000,
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(['code' => $coupon['code']], $coupon);
        }
    }
}
