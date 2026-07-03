<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LuckySpinController extends Controller
{
    private $prizes = [
        0 => ['name' => 'Mã giảm giá 50k', 'type' => 'fixed', 'value' => 50000, 'min_order' => 0],
        1 => ['name' => 'Mã giảm giá 100k', 'type' => 'fixed', 'value' => 100000, 'min_order' => 0],
        2 => ['name' => 'Miễn phí vận chuyển', 'type' => 'fixed', 'value' => 0, 'min_order' => 0],
        3 => ['name' => 'Mã giảm giá 200k', 'type' => 'fixed', 'value' => 200000, 'min_order' => 0],
        4 => ['name' => 'Chúc bạn may mắn lần sau', 'type' => 'lose', 'value' => 0, 'min_order' => 0],
        5 => ['name' => 'Mã giảm giá 300k', 'type' => 'fixed', 'value' => 300000, 'min_order' => 0],
        6 => ['name' => 'Mã giảm giá 150k', 'type' => 'fixed', 'value' => 150000, 'min_order' => 0],
        7 => ['name' => 'Mã giảm giá 500k', 'type' => 'fixed', 'value' => 500000, 'min_order' => 0],
    ];

    public function index()
    {
        $hasSpunToday = false;
        if (auth()->check()) {
            $hasSpunToday = UserNotification::where('user_id', auth()->id())
                ->where('title', 'Vòng quay may mắn')
                ->where('created_at', '>=', Carbon::today())
                ->exists();
            
            // Clear any active coupon code in session when visiting the lucky spin page
            session()->forget('coupon_code');
        }

        return view('lucky-spin.index', compact('hasSpunToday'));
    }

    public function spin(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để tham gia vòng quay!'
            ], 401);
        }

        $userId = auth()->id();

        // Clear any active coupon code in session when spinning
        $request->session()->forget('coupon_code');

        // Check if user has already spun today
        $hasSpunToday = UserNotification::where('user_id', $userId)
            ->where('title', 'Vòng quay may mắn')
            ->where('created_at', '>=', Carbon::today())
            ->exists();

        if ($hasSpunToday) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã quay hôm nay rồi. Hãy quay lại vào ngày mai nhé!'
            ], 422);
        }

        // Randomize prize index (0 to 7) with custom weight (total = 1000)
        // Adjusting probabilities to favor higher-value coupons and Free Shipping.
        // Index 4 (Chúc bạn may mắn lần sau) weight is 5, representing exactly 0.5% probability.
        $weights = [
            0 => 10, // 50k (10%)
            1 => 10, // 100k (10%)
            2 => 20, // Freeship (20%)
            3 => 15, // 200k (15%)
            4 => 5,  // Lose (5%)
            5 => 20, // 300k (20%)
            6 => 10, // 150k (10%)
            7 => 10, // 500k (10%)
        ];

        $prizeIndex = $this->weightedRandom($weights);
        $prize = $this->prizes[$prizeIndex];

        $isWin = ($prizeIndex !== 4);
        $couponCode = null;

        if ($isWin) {
            if ($prizeIndex === 2) {
                // Freeship coupon
                $couponCode = 'LUCKYFREE-' . strtoupper(Str::random(6));
            } else {
                $couponCode = 'LUCKY' . ($prize['value'] / 1000) . 'K-' . strtoupper(Str::random(6));
            }

            Coupon::create([
                'code' => $couponCode,
                'type' => 'fixed',
                'value' => $prize['value'],
                'min_order' => $prize['min_order'],
                'usage_limit' => 1,
                'used_count' => 0,
                'starts_at' => now(),
                'ends_at' => now()->addDays(7), // valid for 7 days
                'is_active' => true,
            ]);

            $notificationBody = "Chúc mừng! Bạn đã quay trúng " . strtolower($prize['name']) . ": {$couponCode}";
        } else {
            $notificationBody = "Rất tiếc! Bạn đã quay trúng ô: Chúc bạn may mắn lần sau. Hãy quay lại vào ngày mai nhé!";
        }

        // Record the spin in notifications to prevent bypass
        UserNotification::create([
            'user_id' => $userId,
            'title' => 'Vòng quay may mắn',
            'body' => $notificationBody,
        ]);

        return response()->json([
            'success' => true,
            'prize_index' => $prizeIndex,
            'prize_name' => $prize['name'],
            'coupon_code' => $couponCode,
            'is_win' => $isWin
        ]);
    }

    private function weightedRandom(array $weights): int
    {
        $r = rand(1, array_sum($weights));
        $current = 0;
        foreach ($weights as $index => $weight) {
            $current += $weight;
            if ($r <= $current) {
                return $index;
            }
        }
        return 0;
    }
}
