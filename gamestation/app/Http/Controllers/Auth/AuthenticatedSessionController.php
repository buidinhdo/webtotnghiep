<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        $action = $request->query('next_action');
        $productId = (int) $request->query('product_id', 0);
        $quantity = max(1, (int) $request->query('quantity', 1));

        if (in_array($action, ['cart_add', 'buy_now'], true) && $productId > 0) {
            $request->session()->put('post_login_action', [
                'action' => $action,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $postLoginAction = $request->session()->pull('post_login_action');

        // Check if user is admin
        if (Auth::check() && Auth::user()->is_admin) {
            return redirect('/admin/dashboard');
        }

        if (is_array($postLoginAction)) {
            $action = $postLoginAction['action'] ?? null;
            $productId = (int) ($postLoginAction['product_id'] ?? 0);
            $quantity = max(1, (int) ($postLoginAction['quantity'] ?? 1));

            if (in_array($action, ['cart_add', 'buy_now'], true) && $productId > 0) {
                $product = Product::find($productId);

                if (!$product) {
                    return redirect()->route('products.index')->with('error', 'Sản phẩm không tồn tại hoặc đã bị xóa.');
                }

                $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

                $item = CartItem::firstOrNew([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                ]);

                $item->price = $product->price;
                $item->quantity = ($item->exists ? $item->quantity : 0) + $quantity;
                $item->save();

                if ($action === 'buy_now') {
                    return redirect()->route('checkout.index')->with('success', 'Đã thêm sản phẩm vào giỏ hàng. Vui lòng kiểm tra và đặt hàng.');
                }

                return redirect()->route('cart.index')->with('success', 'Đã thêm vào giỏ hàng.');
            }
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
