<?php

return [
    // Shop location used to compute shipping distance.
    'shop_address' => env('SHOP_ADDRESS', '123 Nguyễn Huệ, TP. HCM'),
    'shop_lat' => env('SHOP_LAT', 10.774158),
    'shop_lng' => env('SHOP_LNG', 106.702213),

    // Fee calculation (VND).
    'min_fee' => (int) env('SHIPPING_MIN_FEE', 15000),
    'fee_per_km' => (int) env('SHIPPING_FEE_PER_KM', 3500),
    'express_surcharge' => (int) env('SHIPPING_EXPRESS_SURCHARGE', 15000),

    // Keep the previous rule: if coupon is applied, shipping fee is free.
    'free_with_coupon' => (bool) env('SHIPPING_FREE_WITH_COUPON', false),

    // Fallback when distance API/geocoding is unavailable.
    'fallback_distance_km' => (float) env('SHIPPING_FALLBACK_DISTANCE_KM', 5),
];
