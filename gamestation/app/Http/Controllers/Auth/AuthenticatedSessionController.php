<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\LoginOtpMail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
     * Handle an incoming authentication request & dispatch OTP.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Validate credentials & check active state
        $user = $request->validateCredentials();

        // If user is Admin, log in directly without OTP
        if ($user->is_admin) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect('/admin/dashboard')->with('success', 'Đăng nhập trang quản trị thành công!');
        }

        // 2. Generate 6-digit random OTP for regular users
        $otp = sprintf('%06d', random_int(100000, 999999));

        // 3. Store temporary login data in session
        $request->session()->put('login_otp', [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(3)->timestamp,
            'resend_available_at' => now()->addSeconds(60)->timestamp,
            'attempts' => 0,
            'remember' => $request->boolean('remember'),
        ]);

        // 4. Send OTP email
        try {
            Mail::to($user->email)->send(new LoginOtpMail($otp, 3, $user->name));
        } catch (\Throwable $e) {
            Log::error('Failed to send login OTP email: ' . $e->getMessage());
        }

        return redirect()->route('login.otp')->with(
            'status',
            'Mã xác thực OTP đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư!'
        );
    }

    /**
     * Display the OTP verification view.
     */
    public function showOtp(Request $request)
    {
        if (! $request->session()->has('login_otp')) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập tài khoản trước.');
        }

        $otpData = $request->session()->get('login_otp');
        $remainingSeconds = max(0, ($otpData['expires_at'] ?? 0) - now()->timestamp);
        $resendCooldown = max(0, ($otpData['resend_available_at'] ?? 0) - now()->timestamp);
        $email = $otpData['email'] ?? '';

        return view('auth.verify-otp', [
            'email' => $email,
            'maskedEmail' => $email, // Giữ tương thích biến view để hiển thị đầy đủ email
            'remainingSeconds' => $remainingSeconds,
            'resendCooldown' => $resendCooldown,
        ]);
    }

    /**
     * Verify the submitted OTP code and complete login.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ], [
            'otp.required' => 'Vui lòng nhập đầy đủ mã OTP.',
            'otp.size' => 'Mã OTP phải gồm đúng 6 chữ số.',
        ]);

        if (! $request->session()->has('login_otp')) {
            return redirect()->route('login')->with('error', 'Phiên xác thực đã hết hạn. Vui lòng đăng nhập lại.');
        }

        $otpData = $request->session()->get('login_otp');

        // Check if exceeded max attempts
        if (($otpData['attempts'] ?? 0) >= 5) {
            $request->session()->forget('login_otp');
            return redirect()->route('login')->with('error', 'Bạn đã nhập sai mã OTP quá 5 lần. Vui lòng đăng nhập lại.');
        }

        // Check if OTP has expired
        if (now()->timestamp > ($otpData['expires_at'] ?? 0)) {
            return back()->with('error', 'Mã OTP đã hết hiệu lực. Vui lòng bấm nút "Gửi lại mã OTP".');
        }

        // Check OTP correctness
        if ($request->input('otp') !== $otpData['otp']) {
            $otpData['attempts'] = ($otpData['attempts'] ?? 0) + 1;
            $request->session()->put('login_otp', $otpData);
            $remaining = max(0, 5 - $otpData['attempts']);

            if ($remaining === 0) {
                $request->session()->forget('login_otp');
                return redirect()->route('login')->with('error', 'Bạn đã nhập sai mã OTP quá 5 lần. Vui lòng đăng nhập lại từ đầu.');
            }

            return back()->with('error', "Mã OTP không chính xác. Bạn còn {$remaining} lần thử.");
        }

        // OTP is valid -> Log the user in
        $userId = $otpData['user_id'];
        $remember = $otpData['remember'] ?? false;
        $request->session()->forget('login_otp');

        Auth::loginUsingId($userId, $remember);
        $request->session()->regenerate();

        $user = Auth::user();
        $postLoginAction = $request->session()->pull('post_login_action');

        // Check if user is admin
        if ($user && $user->is_admin) {
            return redirect('/admin/dashboard');
        }

        if (is_array($postLoginAction)) {
            $action = $postLoginAction['action'] ?? null;
            $productId = (int) ($postLoginAction['product_id'] ?? 0);
            $quantity = max(1, (int) ($postLoginAction['quantity'] ?? 1));

            if (in_array($action, ['cart_add', 'buy_now'], true) && $productId > 0) {
                $product = Product::find($productId);

                if (! $product) {
                    return redirect()->route('products.index')->with('error', 'Sản phẩm không tồn tại hoặc đã bị xóa.');
                }

                $cart = Cart::firstOrCreate(['user_id' => $user->id]);

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

        return redirect()->intended(RouteServiceProvider::HOME)->with('success', 'Đăng nhập thành công!');
    }

    /**
     * Resend a fresh OTP code.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        if (! $request->session()->has('login_otp')) {
            return redirect()->route('login')->with('error', 'Phiên xác thực đã hết hạn. Vui lòng đăng nhập lại.');
        }

        $otpData = $request->session()->get('login_otp');

        // Check resend cooldown
        if (now()->timestamp < ($otpData['resend_available_at'] ?? 0)) {
            $waitSec = ($otpData['resend_available_at'] - now()->timestamp);
            return back()->with('error', "Vui lòng đợi {$waitSec} giây trước khi gửi lại mã OTP.");
        }

        $user = User::find($otpData['user_id']);
        if (! $user || ! $user->is_active) {
            $request->session()->forget('login_otp');
            return redirect()->route('login')->with('error', 'Tài khoản không hợp lệ hoặc đã bị khóa.');
        }

        $otp = sprintf('%06d', random_int(100000, 999999));
        $otpData['otp'] = $otp;
        $otpData['expires_at'] = now()->addMinutes(3)->timestamp;
        $otpData['resend_available_at'] = now()->addSeconds(60)->timestamp;
        $otpData['attempts'] = 0;
        $request->session()->put('login_otp', $otpData);

        try {
            Mail::to($user->email)->send(new LoginOtpMail($otp, 3, $user->name));
        } catch (\Throwable $e) {
            Log::error('Failed to resend login OTP email: ' . $e->getMessage());
        }

        return back()->with('status', 'Mã OTP mới đã được gửi đến email của bạn.');
    }

    /**
     * Cancel the OTP authentication process.
     */
    public function cancelOtp(Request $request): RedirectResponse
    {
        $request->session()->forget('login_otp');
        return redirect()->route('login');
    }

    /**
     * Helper to mask email address for display.
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];
        $len = strlen($name);

        if ($len <= 2) {
            $maskedName = substr($name, 0, 1) . '*';
        } elseif ($len <= 4) {
            $maskedName = substr($name, 0, 1) . '**' . substr($name, -1);
        } else {
            $maskedName = substr($name, 0, 2) . str_repeat('*', min(5, $len - 4)) . substr($name, -2);
        }

        return $maskedName . '@' . $domain;
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
