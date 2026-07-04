<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\UserNotification;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $cart = Cart::with('items.product.primaryImage')->firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        $coupon = $this->resolveCoupon($request, $cart);

        $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);
        $discount = 0;
        if ($coupon) {
            $discount = $coupon->type === 'percent'
                ? round($subtotal * ($coupon->value / 100), 2)
                : (float) $coupon->value;
        }

        // Default initial shipping fee
        $initialShippingFee = $this->calculateShippingFee('Hồ Chí Minh', 'Quận 1', 'Phường Bến Nghé', $coupon, 'standard', old('payment_method', ''));
        $initialTotal = max(0, $subtotal - $discount) + $initialShippingFee;

        return view('checkout.index', compact(
            'cart',
            'coupon',
            'initialShippingFee',
            'initialTotal'
        ));
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:30'],
            'province' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'ward' => ['required', 'string', 'max:255'],
            'detail' => ['required', 'string', 'max:255'],
            'shipping_method' => ['required', 'string', 'in:standard,express'],
            'payment_method' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        
        $province = $request->input('province');
        $district = $request->input('district');
        $ward = $request->input('ward');
        $detail = $request->input('detail');
        $fullAddress = "{$detail}, {$ward}, {$district}, {$province}";
        
        $data = [
            'shipping_name' => $request->input('shipping_name'),
            'shipping_phone' => $request->input('shipping_phone'),
            'shipping_address' => $fullAddress,
            'shipping_method' => $request->input('shipping_method'),
            'payment_method' => $request->input('payment_method'),
            'notes' => $request->input('notes'),
        ];

        $cart = Cart::with('items.product')->firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        if ($cart->items->isEmpty()) {
            return back()->with('error', 'Gio hang dang trong.');
        }

        foreach ($cart->items as $item) {
            if ($item->quantity > $item->product->stock) {
                return back()->with('error', "San pham {$item->product->name} khong du ton kho.");
            }
        }

        $coupon = $this->resolveCoupon($request, $cart);

        // Update user profile phone and address with the latest checkout information
        $request->user()->update([
            'phone' => $data['shipping_phone'],
            'address' => $data['shipping_address'],
        ]);

        $order = DB::transaction(function () use ($request, $cart, $coupon, $data, $province, $district, $ward) {
            $subtotal = $cart->items->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            $discount = 0;
            if ($coupon) {
                $discount = $coupon->type === 'percent'
                    ? round($subtotal * ($coupon->value / 100), 2)
                    : (float) $coupon->value;
            }

            $shop = $this->resolveShopLocation();
            $customer = $this->geocodeAddress($data['shipping_address']);
            $distanceKm = $this->resolveDistanceKm($shop, $customer);
            $shippingFee = $this->calculateShippingFee($province, $district, $ward, $coupon, $data['shipping_method'], $data['payment_method']);
            $total = max(0, $subtotal - $discount) + $shippingFee;

            $order = Order::create([
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'unpaid',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_fee' => $shippingFee,
                'shipping_distance_km' => round($distanceKm, 2),
                'total' => $total,
                'coupon_code' => $coupon?->code,
                'shipping_name' => $data['shipping_name'],
                'shipping_phone' => $data['shipping_phone'],
                'shipping_address' => $data['shipping_address'],
                'shipping_method' => $data['shipping_method'],
                'notes' => $data['notes'] ?? null,
                'placed_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->price * $item->quantity,
                ]);

                Product::where('id', $item->product_id)
                    ->decrement('stock', $item->quantity);
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            $cart->items()->delete();
            $request->session()->forget('coupon_code');

            UserNotification::create([
                'user_id' => $request->user()->id,
                'title' => 'Đơn hàng mới',
                'body' => "Đơn hàng {$order->id} đã được tạo thành công.",
            ]);

            return $order;
        });

        if ($order->payment_method === 'credit_card') {
            $vnp_Url = config('vnpay.vnp_Url');
            $vnp_Returnurl = config('vnpay.vnp_ReturnUrl');
            $vnp_TmnCode = config('vnpay.vnp_TmnCode');
            $vnp_HashSecret = config('vnpay.vnp_HashSecret');

            $vnp_TxnRef = $order->id;
            $vnp_OrderInfo = "Thanh toan don hang #" . $order->id;
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = (int)($order->total * 100);
            $vnp_Locale = 'vn';
            $vnp_IpAddr = $request->ip();

            $inputData = [
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
            ];

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }

            return redirect()->away($vnp_Url);
        }

        return redirect()->route('orders.show', $order)->with('success', 'Đặt hàng thành công.');
    }

    public function calculateShippingFeeApi(Request $request)
    {
        $request->validate([
            'shipping_address' => ['required', 'string', 'max:255'],
            'shipping_method' => ['required', 'string', 'in:standard,express'],
            'payment_method' => ['nullable', 'string'],
        ]);

        $cart = Cart::with('items.product')->firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        $subtotal = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $coupon = $this->resolveCoupon($request, $cart);
        $discount = 0;
        if ($coupon) {
            $discount = $coupon->type === 'percent'
                ? round($subtotal * ($coupon->value / 100), 2)
                : (float) $coupon->value;
        }

        $shop = $this->resolveShopLocation();
        $customer = $this->geocodeAddress($request->shipping_address);
        $distanceKm = $this->resolveDistanceKm($shop, $customer);
        
        $addressParts = array_map('trim', explode(',', $request->shipping_address));
        $province = count($addressParts) >= 1 ? end($addressParts) : 'Hồ Chí Minh';
        $district = count($addressParts) >= 2 ? $addressParts[count($addressParts) - 2] : 'Quận 1';
        $ward = count($addressParts) >= 3 ? $addressParts[count($addressParts) - 3] : 'Phường Bến Nghé';
        
        $shippingFee = $this->calculateShippingFee($province, $district, $ward, $coupon, $request->shipping_method, $request->payment_method);
        $total = max(0, $subtotal - $discount) + $shippingFee;

        return response()->json([
            'success' => true,
            'distance_km' => round($distanceKm, 2),
            'shipping_fee' => $shippingFee,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'formatted_shipping_fee' => number_format($shippingFee, 0, ',', '.') . 'đ',
            'formatted_total' => number_format($total, 0, ',', '.') . 'đ',
            'formatted_discount' => number_format($discount, 0, ',', '.') . 'đ',
        ]);
    }

    protected function resolveCoupon(Request $request, Cart $cart): ?Coupon
    {
        $code = $request->session()->get('coupon_code');
        if (!$code) {
            return null;
        }

        $subtotal = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $coupon = Coupon::where('code', $code)->first();
        if (!$coupon || !$coupon->isValidForAmount($subtotal)) {
            $request->session()->forget('coupon_code');
            return null;
        }

        return $coupon;
    }

    private function resolveShopLocation(): array
    {
        $shopAddress = \App\Models\Setting::get('store_address', config('shipping.shop_address'));

        if ($shopAddress === config('shipping.shop_address')) {
            $configuredLat = config('shipping.shop_lat');
            $configuredLng = config('shipping.shop_lng');

            if ($configuredLat !== null && $configuredLng !== null) {
                return [
                    'lat' => (float) $configuredLat,
                    'lng' => (float) $configuredLng,
                    'address' => $shopAddress,
                ];
            }
        }

        $geo = $this->geocodeAddress($shopAddress);

        return [
            'lat' => $geo['lat'] ?? 0.0,
            'lng' => $geo['lng'] ?? 0.0,
            'address' => $shopAddress,
        ];
    }

    private function geocodeAddress(string $address): array
    {
        $result = $this->queryNominatim($address);
        if ($result) {
            return $result;
        }

        $parts = array_map('trim', explode(',', $address));
        while (count($parts) > 1) {
            array_shift($parts);
            $subAddress = implode(', ', $parts);
            $result = $this->queryNominatim($subAddress);
            if ($result) {
                return $result;
            }
        }

        return [];
    }

    private function queryNominatim(string $query): array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'GameStation/1.0'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                ]);

            if (!$response->successful()) {
                return [];
            }

            $first = $response->json()[0] ?? null;
            if (!$first) {
                return [];
            }

            return [
                'lat' => isset($first['lat']) ? (float) $first['lat'] : null,
                'lng' => isset($first['lon']) ? (float) $first['lon'] : null,
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function resolveDistanceKm(array $shop, array $customer): float
    {
        $shopLat = $shop['lat'] ?? null;
        $shopLng = $shop['lng'] ?? null;
        $customerLat = $customer['lat'] ?? null;
        $customerLng = $customer['lng'] ?? null;

        if ($shopLat === null || $shopLng === null || $customerLat === null || $customerLng === null || ($shopLat == 0.0 && $shopLng == 0.0)) {
            return (float) config('shipping.fallback_distance_km', 5);
        }

        $routeKm = $this->fetchDrivingDistanceKm((float) $shopLat, (float) $shopLng, (float) $customerLat, (float) $customerLng);
        if ($routeKm !== null) {
            return $routeKm;
        }

        return $this->haversineDistanceKm((float) $shopLat, (float) $shopLng, (float) $customerLat, (float) $customerLng);
    }

    private function fetchDrivingDistanceKm(float $fromLat, float $fromLng, float $toLat, float $toLng): ?float
    {
        try {
            $url = sprintf(
                'https://router.project-osrm.org/route/v1/driving/%F,%F;%F,%F',
                $fromLng,
                $fromLat,
                $toLng,
                $toLat
            );

            $response = Http::timeout(8)->get($url, [
                'overview' => 'false',
                'alternatives' => 'false',
            ]);

            if (!$response->successful()) {
                return null;
            }

            $meters = data_get($response->json(), 'routes.0.distance');
            if (!is_numeric($meters)) {
                return null;
            }

            return max(0.1, round(((float) $meters) / 1000, 2));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function haversineDistanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return max(0.1, round($earthRadius * $c, 2));
    }

    private function calculateShippingFee(string $province, string $district, string $ward, ?Coupon $coupon, string $shippingMethod, ?string $paymentMethod = ''): float
    {
        if ($paymentMethod === 'card' || $paymentMethod === 'credit_card') {
            return 0.0;
        }

        $freeWithCoupon = (bool) config('shipping.free_with_coupon', false);
        $isFreeShipCoupon = $coupon && str_starts_with($coupon->code, 'LUCKYFREE-');

        if (($coupon && $freeWithCoupon) || $isFreeShipCoupon) {
            return 0;
        }

        $region = $this->resolveRegion($province);

        // 1. Base fee by region for Standard shipping
        $baseFee = match ($region) {
            'local' => 14750.0,    // Local TP. HCM base: e.g. 14.750đ
            'south' => 25000.0,    // Miền Nam base: e.g. 25.000đ
            'central' => 45250.0,  // Miền Trung base: e.g. 45.250đ
            'north' => 55500.0,    // Miền Bắc base: e.g. 55.500đ
            default => 45250.0,
        };

        // 2. Surcharge based on District level (urban vs rural)
        $districtLower = mb_strtolower($district);
        $districtSurcharge = 0.0;
        if (str_contains($districtLower, 'huyện')) {
            $districtSurcharge = 5250.0; // Huyện: phụ phí đường xa
        } elseif (str_contains($districtLower, 'quận') || str_contains($districtLower, 'thành phố') || str_contains($districtLower, 'thị xã')) {
            $districtSurcharge = 1500.0; // Thành thị trung tâm
        }

        // 3. Surcharge based on Ward/Commune level
        $wardLower = mb_strtolower($ward);
        $wardSurcharge = 0.0;
        if (str_contains($wardLower, 'xã')) {
            $wardSurcharge = 3750.0;     // Xã ở nông thôn
        } elseif (str_contains($wardLower, 'thị trấn')) {
            $wardSurcharge = 2500.0;     // Thị trấn
        } elseif (str_contains($wardLower, 'phường')) {
            $wardSurcharge = 1000.0;     // Phường ở đô thị
        }

        // Calculate standard shipping fee
        $standardFee = $baseFee + $districtSurcharge + $wardSurcharge;

        // 4. Express delivery fee (Standard fee + Express surcharge based on region)
        if ($shippingMethod === 'express') {
            $expressSurcharge = match ($region) {
                'local' => 10000.0,    // Local Express: Standard + 10.000đ
                'south' => 20000.0,    // South Express: Standard + 20.000đ
                'central' => 25000.0,   // Central Express: Standard + 25.000đ
                'north' => 40000.0,    // North Express: Standard + 40.000đ
                default => 25000.0,
            };
            return round($standardFee + $expressSurcharge, 2);
        }

        return round($standardFee, 2);
    }

    private function resolveRegion(string $province): string
    {
        $province = mb_strtolower($province);
        // Remove common prefixes
        $province = str_replace(['tỉnh', 'thành phố', 'tp.', 'tp', 'thành phố hồ chí minh', 'thành phố hà nội', 'thành phố hải phòng', 'thành phố đà nẵng', 'thành phố cần thơ'], '', $province);
        $province = trim($province);

        // North region list
        $north = [
            'hà nội', 'hải phòng', 'hà giang', 'cao bằng', 'bắc kạn', 'tuyên quang', 'lào cai', 'điện biên', 
            'lai châu', 'sơn la', 'yên bái', 'hòa bình', 'thái nguyên', 'lạng sơn', 'quảng ninh', 'bắc giang', 
            'phú thọ', 'vĩnh phúc', 'bắc ninh', 'hải dương', 'hưng yên', 'thái bình', 'hà nam', 'nam định', 'ninh bình'
        ];

        // Central region list
        $central = [
            'đà nẵng', 'thanh hóa', 'nghệ an', 'hà tĩnh', 'quảng bình', 'quảng trị', 'thừa thiên huế', 'quảng nam', 
            'quảng ngãi', 'bình định', 'phú yên', 'khánh hòa', 'ninh thuận', 'bình thuận', 'kon tum', 'gia lai', 
            'đắk lắk', 'đắk nông', 'lâm đồng', 'thừa thiên-huế', 'huế'
        ];

        // South region list
        $south = [
            'bình phước', 'bình dương', 'đồng nai', 'tây ninh', 'bà rịa - vũng tàu', 'long an', 'đồng tháp', 
            'an giang', 'tiền giang', 'bến tre', 'vĩnh long', 'trà vinh', 'hậu giang', 'kiên giang', 'sóc trăng', 
            'bạc liêu', 'cà mau', 'cần thơ', 'bà rịa vũng tàu'
        ];

        // Specific checks
        if (str_contains($province, 'hồ chí minh') || str_contains($province, 'hcm') || str_contains($province, 'sài gòn') || $province === 'hcm' || $province === 'hồ chí minh') {
            return 'local';
        }

        if (in_array($province, $north)) {
            return 'north';
        }

        if (in_array($province, $central)) {
            return 'central';
        }

        if (in_array($province, $south)) {
            return 'south';
        }

        // Substring check fallbacks
        foreach ($north as $n) {
            if (str_contains($province, $n)) return 'north';
        }
        foreach ($central as $c) {
            if (str_contains($province, $c)) return 'central';
        }
        foreach ($south as $s) {
            if (str_contains($province, $s)) return 'south';
        }

        return 'central'; // Default fallback
    }

    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = config('vnpay.vnp_HashSecret');
        $vnp_SecureHash = $request->input('vnp_SecureHash');
        
        $inputData = [];
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $orderId = $request->input('vnp_TxnRef');
        $order = Order::find($orderId);

        if (!$order) {
            return redirect()->route('products.index')->with('error', 'Đơn hàng không tồn tại.');
        }

        if ($secureHash === $vnp_SecureHash) {
            $responseCode = $request->input('vnp_ResponseCode');
            if ($responseCode === '00') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing',
                ]);

                UserNotification::create([
                    'user_id' => $order->user_id,
                    'title' => 'Thanh toán đơn hàng thành công',
                    'body' => "Đơn hàng #{$order->id} đã được thanh toán qua VNPay thành công.",
                ]);

                return redirect()->route('orders.show', $order)->with('success', 'Thanh toán đơn hàng qua VNPay thành công.');
            } else {
                $order->update([
                    'payment_status' => 'failed',
                ]);
                return redirect()->route('orders.show', $order)->with('error', 'Giao dịch thanh toán qua VNPay thất bại.');
            }
        } else {
            return redirect()->route('orders.show', $order)->with('error', 'Chữ ký giao dịch VNPay không hợp lệ.');
        }
    }

    public function vnpayIpn(Request $request)
    {
        $vnp_HashSecret = config('vnpay.vnp_HashSecret');
        $vnp_SecureHash = $request->input('vnp_SecureHash');
        
        $inputData = [];
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        
        try {
            $orderId = $request->input('vnp_TxnRef');
            $order = Order::find($orderId);
            
            if (!$order) {
                return response()->json([
                    'RspCode' => '01',
                    'Message' => 'Order not found'
                ]);
            }
            
            $vnp_Amount = $request->input('vnp_Amount') / 100;
            if ($order->total != $vnp_Amount) {
                return response()->json([
                    'RspCode' => '04',
                    'Message' => 'Invalid amount'
                ]);
            }
            
            if ($order->payment_status !== 'unpaid') {
                return response()->json([
                    'RspCode' => '02',
                    'Message' => 'Order already confirmed'
                ]);
            }
            
            if ($secureHash === $vnp_SecureHash) {
                $responseCode = $request->input('vnp_ResponseCode');
                if ($responseCode === '00') {
                    $order->update([
                        'payment_status' => 'paid',
                        'status' => 'processing',
                    ]);

                    UserNotification::create([
                        'user_id' => $order->user_id,
                        'title' => 'Thanh toán đơn hàng thành công (IPN)',
                        'body' => "Đơn hàng #{$order->id} đã nhận xác nhận thanh toán qua VNPay.",
                    ]);
                } else {
                    $order->update([
                        'payment_status' => 'failed',
                    ]);
                }
                
                return response()->json([
                    'RspCode' => '00',
                    'Message' => 'Confirm success'
                ]);
            } else {
                return response()->json([
                    'RspCode' => '97',
                    'Message' => 'Invalid signature'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'RspCode' => '99',
                'Message' => 'Unknown error'
            ]);
        }
    }
}
