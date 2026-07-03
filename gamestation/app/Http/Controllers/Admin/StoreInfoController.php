<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class StoreInfoController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function edit()
    {
        $settings = [
            'store_name' => Setting::get('store_name', 'GameStation'),
            'store_hotline' => Setting::get('store_hotline', '0900 000 000'),
            'store_email' => Setting::get('store_email', 'support@gamestation.test'),
            'store_address_hcm' => Setting::get('store_address_hcm', '123 Nguyễn Huệ, TP. HCM'),
            'store_address_hn' => Setting::get('store_address_hn', '88 Consoles Road, Hai Bà Trưng'),
            'store_address' => Setting::get('store_address', config('shipping.shop_address')),
            'store_description' => Setting::get('store_description', 'Hệ thống bán lẻ game và phụ kiện chính hãng. Cập nhật sản phẩm mới mỗi ngày.'),
        ];

        return view('admin.store-info.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_hotline' => 'required|string|max:50',
            'store_email' => 'required|email|max:255',
            'store_address_hcm' => 'required|string|max:500',
            'store_address_hn' => 'required|string|max:500',
            'store_address' => 'required|string|max:500',
            'store_description' => 'required|string|max:1000',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('admin.store-info.edit')->with('success', 'Cập nhật thông tin cửa hàng thành công!');
    }
}
