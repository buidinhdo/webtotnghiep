<x-guest-layout>
    <section class="mx-auto max-w-xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="gs-auth-panel">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-sky-50 text-sky-600 mb-4 ring-8 ring-sky-50/50">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <span class="gs-auth-kicker">Bảo mật 2 lớp</span>
                <h1 class="gs-auth-title text-2xl font-bold text-slate-800">Xác thực mã OTP</h1>
                <p class="gs-auth-subtitle mt-2 text-sm text-slate-600">
                    Mã xác thực gồm 6 chữ số đã được gửi đến email:
                    <br>
                    <span class="font-semibold text-slate-900 bg-slate-100 px-2 py-0.5 rounded mt-1 inline-block">{{ $maskedEmail }}</span>
                </p>
            </div>

            {{-- Flash status or error alerts --}}
            @if (session('status'))
                <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700 flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-700 flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.otp.verify') }}" id="otp-form" class="mt-6 space-y-6">
                @csrf

                <div>
                    <label class="block text-center text-sm font-medium text-slate-700 mb-3">
                        Nhập 6 chữ số mã xác thực
                    </label>

                    <!-- 6-digit OTP Input Boxes -->
                    <div class="flex justify-center items-center gap-2 sm:gap-3" id="otp-boxes">
                        @for ($i = 0; $i < 6; $i++)
                            <input
                                type="text"
                                inputmode="numeric"
                                maxlength="1"
                                pattern="[0-9]*"
                                class="otp-digit w-11 h-14 sm:w-12 sm:h-14 text-center text-2xl font-bold rounded-xl border border-slate-300 bg-white text-slate-900 shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 focus:outline-none transition-all"
                                data-index="{{ $i }}"
                                autocomplete="one-time-code"
                                {{ $i === 0 ? 'autofocus' : '' }}
                            />
                        @endfor
                    </div>

                    <!-- Hidden input to submit complete OTP -->
                    <input type="hidden" name="otp" id="otp-input" value="{{ old('otp') }}">

                    <x-input-error :messages="$errors->get('otp')" class="mt-3 text-center" />
                </div>

                <!-- Expiration Countdown Indicator -->
                <div class="text-center text-xs text-slate-500">
                    <span>Mã OTP hết hạn sau: </span>
                    <span id="expiry-timer" class="font-bold text-sky-600">--:--</span>
                </div>

                <div class="pt-2">
                    <x-primary-button class="w-full justify-center py-3 text-base gs-auth-button shadow-lg shadow-sky-500/20">
                        {{ __('Xác nhận đăng nhập') }}
                    </x-primary-button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm">
                <!-- Resend OTP Form -->
                <form method="POST" action="{{ route('login.otp.resend') }}" id="resend-form" class="inline">
                    @csrf
                    <span class="text-slate-600">Chưa nhận được mã?</span>
                    <button
                        type="submit"
                        id="resend-btn"
                        class="ml-1 text-sky-600 hover:text-sky-700 font-semibold hover:underline disabled:opacity-50 disabled:cursor-not-allowed disabled:no-underline transition-colors"
                        {{ $canResendAfter > 0 ? 'disabled' : '' }}
                    >
                        Gửi lại mã <span id="resend-timer-text">{{ $canResendAfter > 0 ? '(' . $canResendAfter . 's)' : '' }}</span>
                    </button>
                </form>

                <!-- Cancel / Back Form -->
                <form method="POST" action="{{ route('login.otp.cancel') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-slate-500 hover:text-slate-700 transition-colors inline-flex items-center gap-1 hover:underline">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Đăng nhập tài khoản khác
                    </button>
                </form>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const digits = document.querySelectorAll('.otp-digit');
            const hiddenInput = document.getElementById('otp-input');
            const otpForm = document.getElementById('otp-form');

            // Populate digits if old input exists
            if (hiddenInput.value && hiddenInput.value.length === 6) {
                hiddenInput.value.split('').forEach((char, index) => {
                    if (digits[index]) digits[index].value = char;
                });
            }

            function updateHiddenInput() {
                let code = '';
                digits.forEach(digit => code += digit.value);
                hiddenInput.value = code;
                return code;
            }

            digits.forEach((digit, index) => {
                digit.addEventListener('input', function(e) {
                    const val = this.value.replace(/[^0-9]/g, '');
                    this.value = val ? val.slice(-1) : '';

                    const code = updateHiddenInput();

                    if (this.value && index < digits.length - 1) {
                        digits[index + 1].focus();
                    }

                    // Auto-submit if all 6 digits are filled
                    if (code.length === 6) {
                        otpForm.submit();
                    }
                });

                digit.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace') {
                        if (!this.value && index > 0) {
                            digits[index - 1].focus();
                            digits[index - 1].value = '';
                            updateHiddenInput();
                        }
                    } else if (e.key === 'ArrowLeft' && index > 0) {
                        digits[index - 1].focus();
                    } else if (e.key === 'ArrowRight' && index < digits.length - 1) {
                        digits[index + 1].focus();
                    }
                });

                digit.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim();
                    const cleanData = pasteData.replace(/[^0-9]/g, '').slice(0, 6);

                    if (cleanData.length > 0) {
                        cleanData.split('').forEach((char, idx) => {
                            if (digits[idx]) {
                                digits[idx].value = char;
                            }
                        });
                        const code = updateHiddenInput();
                        const nextIndex = Math.min(cleanData.length, digits.length - 1);
                        digits[nextIndex].focus();

                        if (code.length === 6) {
                            otpForm.submit();
                        }
                    }
                });
            });

            // Expiry countdown timer
            let remainingSeconds = {{ $remainingSeconds }};
            const expiryTimerEl = document.getElementById('expiry-timer');

            function updateExpiryDisplay() {
                if (remainingSeconds <= 0) {
                    expiryTimerEl.textContent = 'Đã hết hạn';
                    expiryTimerEl.classList.remove('text-sky-600');
                    expiryTimerEl.classList.add('text-rose-600');
                    return;
                }
                const mins = Math.floor(remainingSeconds / 60);
                const secs = remainingSeconds % 60;
                expiryTimerEl.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                remainingSeconds--;
            }
            updateExpiryDisplay();
            const expiryInterval = setInterval(function() {
                updateExpiryDisplay();
                if (remainingSeconds < 0) {
                    clearInterval(expiryInterval);
                }
            }, 1000);

            // Resend cooldown timer
            let resendCooldown = {{ $canResendAfter }};
            const resendBtn = document.getElementById('resend-btn');
            const resendTimerText = document.getElementById('resend-timer-text');

            if (resendCooldown > 0) {
                const resendInterval = setInterval(function() {
                    resendCooldown--;
                    if (resendCooldown <= 0) {
                        clearInterval(resendInterval);
                        resendBtn.disabled = false;
                        resendTimerText.textContent = '';
                    } else {
                        resendTimerText.textContent = `(${resendCooldown}s)`;
                    }
                }, 1000);
            }
        });
    </script>
</x-guest-layout>
