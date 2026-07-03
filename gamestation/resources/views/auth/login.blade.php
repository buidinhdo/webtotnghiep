<x-guest-layout>
    <section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="gs-auth-panel">
            <span class="gs-auth-kicker">Đăng nhập</span>
            <h1 class="gs-auth-title">Đăng nhập tài khoản</h1>
            <p class="gs-auth-subtitle">Đăng nhập bằng email và mật khẩu để tiếp tục mua sắm.</p>

            <x-auth-session-status class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
                @csrf

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="mt-2 w-full gs-auth-input" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Nhập email của bạn" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('Mật khẩu')" />
                    <div class="relative mt-2">
                        <x-text-input id="password" class="w-full gs-auth-input pr-10" type="password" name="password" required autocomplete="current-password" placeholder="Nhập mật khẩu" />
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors toggle-password" data-target="password">
                            <svg class="h-5 w-5 eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="h-5 w-5 eye-closed hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L5.146 5.146m4.242 4.242L3.62 3.62m3.02 3.02L20.854 20.854M21.542 12c-.783 2.493-2.5 4.544-4.73 5.679m2.73-5.679a9.963 9.963 0 00-1.563-3.028m-1.04-1.139a9.988 9.988 0 00-3.3-1.157M12 5c.42 0 .83.032 1.233.095" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                    <label for="remember_me" class="inline-flex items-center gap-2 text-slate-600">
                        <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500" name="remember">
                        <span>Ghi nhớ đăng nhập</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a class="gs-auth-link" href="{{ route('password.request') }}">Quên mật khẩu?</a>
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
                    <a href="{{ route('register') }}" class="gs-auth-link">Chưa có tài khoản? Đăng ký</a>
                    <x-primary-button class="gs-auth-button">
                        {{ __('Đăng nhập') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-password').forEach(button => {
                const inputId = button.getAttribute('data-target');
                const input = document.getElementById(inputId);

                // Hide button initially
                button.classList.add('invisible');

                // Show button on focus
                input.addEventListener('focus', function() {
                    button.classList.remove('invisible');
                });

                // Hide button on blur with a slight delay to allow click events to fire
                input.addEventListener('blur', function() {
                    setTimeout(() => {
                        if (document.activeElement !== input) {
                            button.classList.add('invisible');
                        }
                    }, 150);
                });

                button.addEventListener('click', function() {
                    const eyeOpen = this.querySelector('.eye-open');
                    const eyeClosed = this.querySelector('.eye-closed');

                    if (input.type === 'password') {
                        input.type = 'text';
                        eyeOpen.classList.add('hidden');
                        eyeClosed.classList.remove('hidden');
                    } else {
                        input.type = 'password';
                        eyeOpen.classList.remove('hidden');
                        eyeClosed.classList.add('hidden');
                    }

                    // Restore focus to input so the button remains visible
                    input.focus();
                });
            });
        });
    </script>
</x-guest-layout>
