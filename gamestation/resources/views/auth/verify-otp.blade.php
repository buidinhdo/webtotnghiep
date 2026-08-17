<x-guest-layout>
    <section class="mx-auto max-w-xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="gs-auth-panel">
            <div class="flex items-center justify-center mb-4">
                <div class="w-14 h-14 rounded-2xl bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-600 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>

            <span class="gs-auth-kicker text-center block text-sky-600 font-semibold tracking-wider uppercase text-xs">Xác thực 2 lớp (2FA)</span>
            <h1 class="gs-auth-title text-center text-2xl font-bold text-slate-900 mt-1">Nhập mã xác thực OTP</h1>
            <p class="gs-auth-subtitle text-center text-slate-500 text-sm mt-2">
                Mã xác thực gồm 6 chữ số đã được gửi đến hộp thư<br>
                <span class="font-semibold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md mt-1 inline-block">{{ $maskedEmail }}</span>
            </p>

            {{-- Flash status / messages --}}
            @if (session('status'))
                <div class="mt-4 p-3.5 rounded-xl border border-emerald-200 bg-emerald-50 text-xs sm:text-sm text-emerald-700 flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mt-4 p-3.5 rounded-xl border border-rose-200 bg-rose-50 text-xs sm:text-sm text-rose-700 flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->has('otp'))
                <div class="mt-4 p-3.5 rounded-xl border border-rose-200 bg-rose-50 text-xs sm:text-sm text-rose-700 flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errors->first('otp') }}</span>
                </div>
            @endif

            <form id="otp-form" method="POST" action="{{ route('login.otp.verify') }}" class="mt-6 space-y-6">
                @csrf
                <input type="hidden" name="otp" id="full-otp" value="">

                <div>
                    <label class="block text-center text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Mã OTP 6 số</label>
                    <div class="flex justify-center gap-2 sm:gap-3" id="otp-inputs-container">
                        @for ($i = 0; $i < 6; $i++)
                            <input
                                type="text"
                                inputmode="numeric"
                                maxlength="1"
                                pattern="[0-9]*"
                                class="otp-digit w-11 h-13 sm:w-13 sm:h-14 text-center text-xl sm:text-2xl font-bold font-mono rounded-xl border-2 border-slate-200 bg-white text-slate-800 shadow-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all outline-none"
                                data-index="{{ $i }}"
                                autocomplete="off"
                                required
                            />
                        @endfor
                    </div>
                </div>

                {{-- Countdown timer --}}
                <div class="text-center">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-xs text-slate-600 font-medium" id="timer-badge">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Mã hết hạn sau: <strong id="countdown-text" class="text-sky-600 font-mono font-bold">03:00</strong></span>
                    </div>
                </div>

                <div class="space-y-3 pt-2">
                    <button type="submit" id="btn-submit-otp" class="w-full py-3 px-4 rounded-xl bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-semibold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Xác nhận & Đăng nhập</span>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm">
                <form method="POST" action="{{ route('login.otp.resend') }}" id="resend-form">
                    @csrf
                    <button type="submit" id="btn-resend" class="text-sky-600 hover:text-sky-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span id="resend-label">Gửi lại mã OTP</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('login.otp.cancel') }}" id="cancel-form">
                    @csrf
                    <button type="submit" class="text-slate-500 hover:text-slate-700 transition-colors">
                        ← Quay lại đăng nhập
                    </button>
                </form>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.otp-digit');
            const fullOtpInput = document.getElementById('full-otp');
            const form = document.getElementById('otp-form');
            const countdownEl = document.getElementById('countdown-text');
            const timerBadge = document.getElementById('timer-badge');
            const btnResend = document.getElementById('btn-resend');
            const resendLabel = document.getElementById('resend-label');

            if (inputs.length > 0) {
                inputs[0].focus();
            }

            // Sync all input boxes to fullOtp hidden input
            function updateFullOtp() {
                let otp = '';
                inputs.forEach(input => {
                    otp += input.value.trim();
                });
                fullOtpInput.value = otp;
                return otp;
            }

            inputs.forEach((input, idx) => {
                // Focus & Keydown Navigation
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace') {
                        if (this.value === '' && idx > 0) {
                            inputs[idx - 1].focus();
                            inputs[idx - 1].value = '';
                        } else {
                            this.value = '';
                        }
                        updateFullOtp();
                        e.preventDefault();
                    } else if (e.key === 'ArrowLeft' && idx > 0) {
                        inputs[idx - 1].focus();
                    } else if (e.key === 'ArrowRight' && idx < inputs.length - 1) {
                        inputs[idx + 1].focus();
                    }
                });

                // Input event for numbers
                input.addEventListener('input', function(e) {
                    const val = this.value.replace(/[^0-9]/g, '');
                    this.value = val ? val[val.length - 1] : '';

                    if (this.value && idx < inputs.length - 1) {
                        inputs[idx + 1].focus();
                    }

                    const currentOtp = updateFullOtp();
                    if (currentOtp.length === 6) {
                        // Auto submit when 6 digits are fully entered
                        form.submit();
                    }
                });

                // Paste event handler
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pastedData = (e.clipboardData || window.clipboardData).getData('text').trim();
                    const digits = pastedData.replace(/[^0-9]/g, '').slice(0, 6);

                    if (digits.length > 0) {
                        digits.split('').forEach((d, i) => {
                            if (inputs[i]) {
                                inputs[i].value = d;
                            }
                        });
                        const currentOtp = updateFullOtp();
                        const nextFocusIdx = Math.min(digits.length, inputs.length - 1);
                        inputs[nextFocusIdx].focus();

                        if (currentOtp.length === 6) {
                            form.submit();
                        }
                    }
                });
            });

            form.addEventListener('submit', function(e) {
                const otp = updateFullOtp();
                if (otp.length !== 6) {
                    e.preventDefault();
                    alert('Vui lòng nhập đủ 6 chữ số mã OTP.');
                }
            });

            // Countdown Timer
            let remainingSeconds = {{ max(0, $remainingSeconds ?? 180) }};
            let resendCooldown = {{ max(0, $resendCooldown ?? 60) }};

            function formatTime(sec) {
                const m = Math.floor(sec / 60).toString().padStart(2, '0');
                const s = (sec % 60).toString().padStart(2, '0');
                return `${m}:${s}`;
            }

            function updateTimer() {
                if (remainingSeconds > 0) {
                    countdownEl.textContent = formatTime(remainingSeconds);
                    remainingSeconds--;
                } else {
                    countdownEl.textContent = 'Đã hết hạn';
                    countdownEl.classList.remove('text-sky-600');
                    countdownEl.classList.add('text-rose-600');
                    timerBadge.classList.remove('bg-slate-100', 'text-slate-600');
                    timerBadge.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
                }

                if (resendCooldown > 0) {
                    btnResend.disabled = true;
                    resendLabel.textContent = `Gửi lại mã (${resendCooldown}s)`;
                    resendCooldown--;
                } else {
                    btnResend.disabled = false;
                    resendLabel.textContent = 'Gửi lại mã OTP';
                }
            }

            updateTimer();
            const timerInterval = setInterval(() => {
                updateTimer();
                if (remainingSeconds <= 0 && resendCooldown <= 0) {
                    // keep resend enabled
                }
            }, 1000);
        });
    </script>
</x-guest-layout>
