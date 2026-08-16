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
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $user = $request->validateCredentials();

        // Admin accounts log in directly without OTP
        if ($user->is_admin) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect('/admin/dashboard');
        }

        // Regular user accounts require OTP verification via Gmail
        $otpCode = sprintf('%06d', random_int(0, 999999));

        $request->session()->put('login_otp', [
            'user_id' => $user->id,
            'email' => $user->email,
            'code' => $otpCode,
            'remember' => $request->boolean('remember'),
            'expires_at' => now()->addMinutes(5)->timestamp,
            'last_sent_at' => now()->timestamp,
        ]);

        try {
            Mail::to($user->email)->send(new LoginOtpMail($otpCode, $user->name));
        } catch (\Throwable $e) {
            Log::error('Failed to send login OTP email: ' . $e->getMessage());
        }

        return redirect()->route('login.otp');
    }

    /**
     * Display the OTP verification view.
     */
    public function showOtp(Request $request): View|RedirectResponse
    {
        $otpData = $request->session()->get('login_otp');

        if (! $otpData || empty($otpData['user_id'])) {
            return redirect()->route('login')->with('error', 'Phiên đăng nhập đã hết hạn hoặc không tồn tại. Vui lòng đăng nhập lại.');
        }

        // Mask email for display (e.g. bu****52@gmail.com)
        $email = $otpData['email'];
        $parts = explode('@', $email);
        $namePart = $parts[0] ?? '';
        $domainPart = $parts[1] ?? '';

        if (strlen($namePart) <= 3) {
            $maskedName = substr($namePart, 0, 1) . '***';
        } else {
            $maskedName = substr($namePart, 0, 2) . str_repeat('*', max(1, strlen($namePart) - 4)) . substr($namePart, -2);
        }
        $maskedEmail = $maskedName . '@' . $domainPart;

        $expiresAt = $otpData['expires_at'] ?? now()->timestamp;
        $remainingSeconds = max(0, $expiresAt - now()->timestamp);
        $canResendAfter = max(0, ($otpData['last_sent_at'] ?? 0) + 60 - now()->timestamp);

        return view('auth.otp-verify', [
            'maskedEmail' => $maskedEmail,
            'remainingSeconds' => $remainingSeconds,
            'canResendAfter' => $canResendAfter,
        ]);
    }

    /**
     * Verify the OTP code and log the user in.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ], [
            'otp.required' => 'Vui lòng nhập đầy đủ mã OTP 6 chữ số.',
            'otp.size' => 'Mã OTP phải gồm đúng 6 chữ số.',
        ]);

        $otpData = $request->session()->get('login_otp');

        if (! $otpData || empty($otpData['user_id'])) {
            return redirect()->route('login')->with('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
        }

        if (now()->timestamp > $otpData['expires_at']) {
            return back()->withErrors(['otp' => 'Mã OTP đã hết hạn. Vui lòng bấm "Gửi lại mã" để nhận mã mới.']);
        }

        if (trim($request->otp) !== (string) $otpData['code']) {
            return back()->withErrors(['otp' => 'Mã OTP không chính xác. Vui lòng kiểm tra lại hộp thư email của bạn.']);
        }

        $user = User::find($otpData['user_id']);

        if (! $user || ! $user->is_active) {
            $request->session()->forget('login_otp');
            return redirect()->route('login')->with('error', 'Tài khoản không tồn tại hoặc đã bị khóa.');
        }

        // Successfully verified: clear OTP session and login user
        $remember = $otpData['remember'] ?? false;
        $request->session()->forget('login_otp');
        Auth::login($user, $remember);
        $request->session()->regenerate();

        $postLoginAction = $request->session()->pull('post_login_action');

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

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Resend a new OTP code to the user's email.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $otpData = $request->session()->get('login_otp');

        if (! $otpData || empty($otpData['user_id'])) {
            return redirect()->route('login')->with('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
        }

        $lastSentAt = $otpData['last_sent_at'] ?? 0;
        if (now()->timestamp - $lastSentAt < 60) {
            $wait = 60 - (now()->timestamp - $lastSentAt);
            return back()->with('error', "Vui lòng đợi {$wait} giây trước khi yêu cầu gửi lại mã OTP.");
        }

        $user = User::find($otpData['user_id']);
        if (! $user) {
            return redirect()->route('login')->with('error', 'Tài khoản không tồn tại.');
        }

        $newOtpCode = sprintf('%06d', random_int(0, 999999));
        $otpData['code'] = $newOtpCode;
        $otpData['expires_at'] = now()->addMinutes(5)->timestamp;
        $otpData['last_sent_at'] = now()->timestamp;
        $request->session()->put('login_otp', $otpData);

        try {
            Mail::to($user->email)->send(new LoginOtpMail($newOtpCode, $user->name));
        } catch (\Throwable $e) {
            Log::error('Failed to resend login OTP email: ' . $e->getMessage());
            return back()->with('error', 'Không thể gửi email OTP lúc này. Vui lòng thử lại sau.');
        }

        return back()->with('status', 'Mã xác thực OTP mới đã được gửi tới email của bạn!');
    }

    /**
     * Cancel the OTP verification process.
     */
    public function cancelOtp(Request $request): RedirectResponse
    {
        $request->session()->forget('login_otp');
        return redirect()->route('login');
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
