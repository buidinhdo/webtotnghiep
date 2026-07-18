<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $this->getCart($request);
        $cart->load('items.product.primaryImage');

        if ($cart->items->isEmpty()) {
            $request->session()->forget('coupon_code');
        }

        return view('cart.index', compact('cart'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $cart = $this->getCart($request);

        $item = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        $item->price = $product->price;
        $item->quantity = ($item->exists ? $item->quantity : 0) + ($data['quantity'] ?? 1);
        $item->save();

        // Nếu là yêu cầu mua ngay thì chuyển tới trang thanh toán
        if ($request->has('buy_now') && $request->input('buy_now')) {
            return redirect()->route('checkout.index')->with('success', 'Đã thêm sản phẩm vào giỏ hàng. Vui lòng kiểm tra và đặt hàng.');
        }

        return redirect()->route('cart.index')->with('success', 'Đã thêm vào giỏ hàng.');
    }

    public function update(Request $request, CartItem $item)
    {
        $this->authorizeCartItem($request, $item);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item->update(['quantity' => $data['quantity']]);

        return back()->with('success', 'Cập nhật giỏ hàng thành công.');
    }

    public function remove(Request $request, CartItem $item)
    {
        $this->authorizeCartItem($request, $item);
        $item->delete();

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    public function applyCoupon(Request $request)
    {
        $data = $request->validate([
            'coupon' => ['required', 'string'],
        ]);

        $code = strtoupper(trim($data['coupon']));
        $coupon = \App\Models\Coupon::where('code', $code)->first();

        if (!$coupon) {
            $request->session()->forget('coupon_code');
            return back()->with('error', 'Mã giảm giá không tồn tại.');
        }

        $cart = $this->getCart($request);
        $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);

        if (!$coupon->isValidForAmount($subtotal)) {
            $request->session()->forget('coupon_code');
            return back()->with('error', 'Mã giảm giá không đủ điều kiện áp dụng cho giỏ hàng hiện tại.');
        }

        $request->session()->put('coupon_code', $code);

        return back()->with('success', 'Đã áp dụng mã giảm giá.');
    }

    protected function getCart(Request $request): Cart
    {
        $user = $request->user();

        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    protected function authorizeCartItem(Request $request, CartItem $item): void
    {
        if ($item->cart->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
