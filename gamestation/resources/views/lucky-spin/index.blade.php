<x-app-layout>
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[1.2fr_1fr] items-center">
            
            <!-- Wheel Container (Left) -->
            <div class="flex flex-col items-center justify-center gs-card p-8 bg-white/80 backdrop-blur-md relative overflow-hidden min-h-[500px]">
                <!-- Outer glow and indicator -->
                <div class="relative w-[340px] h-[340px] sm:w-[400px] sm:h-[400px] flex items-center justify-center">
                    
                    <!-- Top Pin/Indicator pointer -->
                    <div class="absolute top-[-15px] z-20 w-8 h-10 drop-shadow-md">
                        <svg viewBox="0 0 24 30" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full filter drop-shadow">
                            <path d="M12 30C12 30 24 18 24 12C24 5.37258 18.6274 0 12 0C5.37258 0 0 5.37258 0 12C0 18 12 30 12 30Z" fill="#ef4444"/>
                            <circle cx="12" cy="12" r="5" fill="#ffffff"/>
                        </svg>
                    </div>

                    <!-- Canvas Wheel -->
                    <canvas id="wheelCanvas" width="400" height="400" class="w-full h-full rounded-full border-8 border-slate-900 shadow-2xl transition-transform duration-[5000ms] ease-out will-change-transform"></canvas>
                    
                    <!-- Center Spin Button / Hub -->
                    <div class="absolute w-16 h-16 sm:w-20 sm:h-20 bg-slate-900 rounded-full border-4 border-white shadow-xl flex items-center justify-center z-10">
                        @auth
                            <button id="spinBtn" class="w-full h-full rounded-full text-white font-bold text-xs sm:text-sm tracking-wide uppercase hover:scale-105 transition bg-gradient-to-tr from-amber-500 to-yellow-400 flex items-center justify-center focus:outline-none">
                                Quay
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="w-full h-full rounded-full text-white font-bold text-[10px] sm:text-xs tracking-wide uppercase hover:scale-105 transition bg-gradient-to-tr from-sky-500 to-indigo-500 flex items-center justify-center text-center px-1">
                                Đăng Nhập
                            </a>
                        @endauth
                    </div>
                </div>

                <p class="mt-6 text-xs text-slate-400 italic">Mỗi tài khoản chỉ được quay tối đa 1 lần mỗi ngày.</p>
            </div>

            <!-- Description & History (Right) -->
            <div class="flex flex-col gap-6">
                <div class="gs-card p-8 bg-gradient-to-br from-slate-900 to-slate-800 text-white">
                    <span class="text-xs uppercase tracking-[0.3em] text-sky-400 font-bold">Mini Game</span>
                    <h1 class="text-3xl font-extrabold mt-2 tracking-tight">Vòng Quay May Mắn</h1>
                    <p class="text-slate-300 mt-4 text-sm leading-relaxed">
                        Chào mừng bạn đến với GameStation Spin! Hãy thử vận may của mình để nhận những phần quà hấp dẫn là các mã giảm giá mua game độc quyền giá trị lên tới <span class="text-amber-400 font-bold">500.000đ</span>.
                    </p>

                    <h3 class="text-sm font-semibold mt-6 uppercase tracking-wider text-slate-400">Cơ cấu giải thưởng:</h3>
                    <ul class="mt-2 space-y-2 text-xs text-slate-300 grid grid-cols-2 gap-x-4">
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Mã giảm giá 500k
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            Mã giảm giá 300k
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                            Mã giảm giá 200k
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                            Mã giảm giá 150k
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                            Mã giảm giá 100k
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Mã giảm giá 50k
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                            Miễn phí vận chuyển
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                            Chúc bạn may mắn lần sau
                        </li>
                    </ul>

                    @auth
                        @if($hasSpunToday)
                            <div class="mt-8 p-4 bg-white/10 rounded-2xl border border-white/10 flex items-center gap-3 text-sm text-yellow-300">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span>Bạn đã tham gia lượt quay của ngày hôm nay. Vui lòng quay lại vào ngày mai!</span>
                            </div>
                        @else
                            <div class="mt-8 p-4 bg-sky-500/20 rounded-2xl border border-sky-500/30 flex items-center gap-3 text-sm text-sky-200">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Bạn có 1 lượt quay miễn phí khả dụng! Nhấn nút "QUAY" ở tâm vòng xoay để bắt đầu.</span>
                            </div>
                        @endif
                    @else
                        <div class="mt-8 text-center">
                            <a href="{{ route('login') }}" class="gs-button w-full">Đăng nhập để quay ngay</a>
                        </div>
                    @endauth
                </div>

                <!-- Game rules -->
                <div class="gs-card p-6 bg-white/80 backdrop-blur-md text-slate-700 text-sm">
                    <h3 class="font-bold text-slate-900 border-b border-slate-100 pb-2 mb-3">Điều khoản & Thể lệ</h3>
                    <ul class="list-disc pl-5 space-y-2 text-xs text-slate-600">
                        <li>Mã giảm giá trúng thưởng sẽ được tạo tự động và lưu vào tài khoản của bạn.</li>
                        <li>Bạn có thể xem lại mã giảm giá của mình bất kỳ lúc nào tại mục **Thông báo**.</li>
                        <li>Mã giảm giá có giá trị sử dụng trong vòng 7 ngày kể từ lúc trúng thưởng.</li>
                        <li>Mỗi đơn hàng chỉ áp dụng tối đa 1 mã giảm giá.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Winner Announcement Modal (Sleek Glassmorphic) -->
    <div id="winnerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4 opacity-0 pointer-events-none transition-opacity duration-300" style="display: none;">
        <div class="bg-white rounded-3xl border border-slate-100 p-8 max-w-md w-full shadow-2xl transform scale-95 transition-transform duration-300 relative text-center">
            
            <!-- Confetti/Emoji visual element -->
            <div id="modalEmojiContainer" class="absolute -top-12 left-1/2 -translate-x-1/2 w-24 h-24 bg-gradient-to-tr from-amber-500 to-yellow-400 rounded-full flex items-center justify-center text-4xl shadow-xl animate-bounce">
                🎉
            </div>

            <h2 id="modalTitle" class="text-2xl font-extrabold text-slate-900 mt-10 mb-2">Chúc Mừng Bạn!</h2>
            <p id="modalSubtext" class="text-slate-500 text-sm mb-6">Bạn đã quay trúng phần quà giá trị sau:</p>

            <!-- Prize banner -->
            <div id="modalPrizeBanner" class="bg-amber-50 border border-amber-200 rounded-2xl py-4 px-6 mb-6">
                <p class="text-amber-800 font-extrabold text-xl" id="modalPrizeName"></p>
            </div>

            <!-- Coupon Code display -->
            <div class="mb-8" id="couponCodeContainer" style="display: none;">
                <p class="text-xs text-slate-400 mb-2 uppercase tracking-widest font-semibold">Mã Giảm Giá Của Bạn</p>
                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden p-2">
                    <span class="flex-1 font-mono font-bold text-slate-800 text-base" id="modalCouponCode">LUCKY-XXXXXX</span>
                    <button id="copyBtn" class="bg-slate-900 hover:bg-slate-800 text-white rounded-xl px-4 py-2 text-xs font-semibold transition">
                        Sao chép
                    </button>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <a href="{{ route('products.index') }}" class="gs-button w-full">Mua sắm ngay</a>
                <button onclick="closeWinnerModal()" class="text-slate-500 hover:text-slate-700 text-sm py-2 font-medium">Đóng</button>
            </div>
        </div>
    </div>

    <!-- Scripts for Canvas Wheel and API -->
    <script>
        const prizes = [
            { name: 'Mã giảm giá 50k', color: '#10b981', textColor: '#ffffff' }, // emerald
            { name: 'Mã giảm giá 100k', color: '#0ea5e9', textColor: '#ffffff' }, // sky
            { name: 'Miễn phí vận chuyển', color: '#14b8a6', textColor: '#ffffff' }, // teal
            { name: 'Mã giảm giá 200k', color: '#ec4899', textColor: '#ffffff' }, // pink
            { name: 'Chúc bạn may mắn lần sau', color: '#64748b', textColor: '#ffffff' }, // slate
            { name: 'Mã giảm giá 300k', color: '#6366f1', textColor: '#ffffff' }, // indigo
            { name: 'Mã giảm giá 150k', color: '#8b5cf6', textColor: '#ffffff' }, // violet
            { name: 'Mã giảm giá 500k', color: '#f59e0b', textColor: '#ffffff' }  // amber
        ];

        // Sound effects (Method B - Direct URLs)
        const spinSound = new Audio('https://tiengdong.com/wp-content/uploads/am-thanh-tro-choi-quay-banh-xe-may-man-www_tiengdong_com.mp3');
        const winSound = new Audio('https://tiengdong.com/wp-content/uploads/Am-thanh-ve-dich-chien-thang-www_tiengdong_com.mp3');
        spinSound.volume = 0.1;
        winSound.volume = 0.15;
        let winSoundTimeout = null;
        let winSoundFadeInterval = null;

        const canvas = document.getElementById('wheelCanvas');
        const ctx = canvas.getContext('2d');
        const count = prizes.length;
        const arc = Math.PI / (count / 2); // slice angle

        // Setup responsive canvas resolution
        function drawWheel() {
            ctx.clearRect(0, 0, 400, 400);
            
            // Draw Slices
            for (let i = 0; i < count; i++) {
                const angle = i * arc;
                ctx.fillStyle = prizes[i].color;
                
                // Draw slice pie
                ctx.beginPath();
                ctx.arc(200, 200, 190, angle, angle + arc, false);
                ctx.lineTo(200, 200);
                ctx.fill();

                // Draw Slice Border
                ctx.strokeStyle = '#e2e8f0';
                ctx.lineWidth = 1;
                ctx.stroke();

                // Draw Text
                ctx.save();
                ctx.translate(200, 200);
                ctx.rotate(angle + arc / 2);
                
                ctx.fillStyle = prizes[i].textColor;
                ctx.font = 'bold 12px "Space Grotesk", sans-serif';
                ctx.textAlign = 'right';
                
                // Truncate text if needed
                const text = prizes[i].name;
                ctx.fillText(text, 175, 5);
                ctx.restore();
            }

            // Draw outer frame border
            ctx.strokeStyle = '#0f172a';
            ctx.lineWidth = 8;
            ctx.beginPath();
            ctx.arc(200, 200, 196, 0, 2 * Math.PI);
            ctx.stroke();
        }

        // Render the wheel initially after fonts are fully loaded
        document.fonts.ready.then(function() {
            drawWheel();
        });

        // Spin Logic
        let isSpinning = false;
        const spinBtn = document.getElementById('spinBtn');

        if (spinBtn) {
            spinBtn.addEventListener('click', function() {
                if (isSpinning) return;
                
                isSpinning = true;
                spinBtn.disabled = true;
                spinBtn.innerText = '...';

                // Call the Laravel backend API using AJAX POST
                fetch("{{ route('lucky-spin.spin') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    if (response.status === 401) {
                        window.location.href = "{{ route('login') }}";
                        throw new Error('Unauthenticated');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        alert(data.message || 'Có lỗi xảy ra.');
                        isSpinning = false;
                        spinBtn.disabled = false;
                        spinBtn.innerText = 'Quay';
                        return;
                    }

                    const prizeIndex = data.prize_index;
                    
                    // Calculation for target rotation:
                    // 1. Minimum 5 full rotations for drama effect (5 * 360 = 1800 deg)
                    // 2. Wheel indicator is at the TOP (270 deg / -90 deg relative to arc center).
                    // We must align the won slice to point to the top.
                    // Slice 0 center is at angle arc/2. The top pin aligns with 270 degrees.
                    const sliceAngle = 360 / count;
                    
                    // To make slice point to top:
                    // Rotation angle = 270 - (prizeIndex * sliceAngle + sliceAngle / 2)
                    const extraRotation = 270 - (prizeIndex * sliceAngle + sliceAngle / 2);
                    const finalRotation = 360 * 5 + extraRotation;

                    // Apply CSS rotation transform
                    canvas.style.transform = `rotate(${finalRotation}deg)`;

                    // Play spinning sound (looping)
                    spinSound.loop = true;
                    spinSound.currentTime = 0;
                    spinSound.play().catch(err => console.log('Audio autoplay blocked:', err));

                    // Wait for the css animation to end (5000ms duration)
                    setTimeout(() => {
                        // Stop and reset spin sound
                        spinSound.pause();
                        spinSound.currentTime = 0;

                        // Play win sound only on winning
                        if (data.is_win) {
                            winSound.currentTime = 0;
                            winSound.volume = 0.15; // Reset volume
                            winSound.play().catch(err => console.log('Audio autoplay blocked:', err));
                            
                            if (winSoundTimeout) clearTimeout(winSoundTimeout);
                            if (winSoundFadeInterval) clearInterval(winSoundFadeInterval);
                            
                            // Fade out volume over 600ms, starting at 2.4s, completely stops at 3.0s
                            winSoundTimeout = setTimeout(() => {
                                const fadeDuration = 600;
                                const fadeInterval = 50;
                                const steps = fadeDuration / fadeInterval;
                                const volumeStep = 0.15 / steps;
                                
                                let fadeCount = 0;
                                winSoundFadeInterval = setInterval(() => {
                                    if (winSound.volume > volumeStep) {
                                        winSound.volume -= volumeStep;
                                    } else {
                                        winSound.volume = 0;
                                    }
                                    fadeCount++;
                                    if (fadeCount >= steps) {
                                        clearInterval(winSoundFadeInterval);
                                        winSoundFadeInterval = null;
                                        winSound.pause();
                                        winSound.currentTime = 0;
                                        winSound.volume = 0.15; // Restore original volume
                                    }
                                }, fadeInterval);
                            }, 2400);
                        }

                        // Display winner modal
                        openWinnerModal(data.prize_name, data.coupon_code, data.is_win);
                        
                        isSpinning = false;
                        spinBtn.innerText = 'Hết lượt';
                    }, 5200);
                })
                .catch(err => {
                    console.error(err);
                    isSpinning = false;
                    spinBtn.disabled = false;
                    spinBtn.innerText = 'Quay';
                });
            });
        }

        // Modal triggers
        const modal = document.getElementById('winnerModal');
        const modalEmojiContainer = document.getElementById('modalEmojiContainer');
        const modalTitle = document.getElementById('modalTitle');
        const modalSubtext = document.getElementById('modalSubtext');
        const modalPrizeBanner = document.getElementById('modalPrizeBanner');
        const modalPrizeName = document.getElementById('modalPrizeName');
        const modalCouponCode = document.getElementById('modalCouponCode');
        const couponContainer = document.getElementById('couponCodeContainer');
        const copyBtn = document.getElementById('copyBtn');

        function openWinnerModal(prizeName, couponCode, isWin) {
            modalPrizeName.innerText = prizeName;
            
            if (isWin) {
                if (modalEmojiContainer) {
                    modalEmojiContainer.innerText = '🎉';
                    modalEmojiContainer.className = 'absolute -top-12 left-1/2 -translate-x-1/2 w-24 h-24 bg-gradient-to-tr from-amber-500 to-yellow-400 rounded-full flex items-center justify-center text-4xl shadow-xl animate-bounce';
                }
                if (modalTitle) modalTitle.innerText = 'Chúc Mừng Bạn!';
                if (modalSubtext) modalSubtext.innerText = 'Bạn đã quay trúng phần quà giá trị sau:';
                if (modalPrizeBanner) {
                    modalPrizeBanner.className = 'bg-amber-50 border border-amber-200 rounded-2xl py-4 px-6 mb-6';
                }
                if (modalPrizeName) {
                    modalPrizeName.className = 'text-amber-800 font-extrabold text-xl';
                }
                if (couponCode) {
                    modalCouponCode.innerText = couponCode;
                    couponContainer.style.display = 'block';
                } else {
                    couponContainer.style.display = 'none';
                }
            } else {
                if (modalEmojiContainer) {
                    modalEmojiContainer.innerText = '🍀';
                    modalEmojiContainer.className = 'absolute -top-12 left-1/2 -translate-x-1/2 w-24 h-24 bg-gradient-to-tr from-slate-400 to-slate-500 rounded-full flex items-center justify-center text-4xl shadow-xl';
                }
                if (modalTitle) modalTitle.innerText = 'Tiếc quá!';
                if (modalSubtext) modalSubtext.innerText = 'Hãy thử lại vào ngày mai nhé!';
                if (modalPrizeBanner) {
                    modalPrizeBanner.className = 'bg-slate-50 border border-slate-200 rounded-2xl py-4 px-6 mb-6';
                }
                if (modalPrizeName) {
                    modalPrizeName.className = 'text-slate-800 font-extrabold text-xl';
                }
                couponContainer.style.display = 'none';
            }

            modal.style.display = 'flex';
            // Trigger animation frame for transition
            requestAnimationFrame(() => {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.firstElementChild.classList.remove('scale-95');
            });
        }

        function closeWinnerModal() {
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.firstElementChild.classList.add('scale-95');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
            
            // Stop winning sound
            if (winSoundTimeout) {
                clearTimeout(winSoundTimeout);
                winSoundTimeout = null;
            }
            if (winSoundFadeInterval) {
                clearInterval(winSoundFadeInterval);
                winSoundFadeInterval = null;
            }
            winSound.pause();
            winSound.currentTime = 0;
            winSound.volume = 0.15; // Reset volume
        }

        // Copy coupon code to clipboard
        if (copyBtn) {
            copyBtn.addEventListener('click', function() {
                const textToCopy = modalCouponCode.innerText;
                navigator.clipboard.writeText(textToCopy)
                    .then(() => {
                        copyBtn.innerText = 'Đã chép!';
                        copyBtn.classList.remove('bg-slate-900');
                        copyBtn.classList.add('bg-emerald-600');
                        
                        setTimeout(() => {
                            copyBtn.innerText = 'Sao chép';
                            copyBtn.classList.remove('bg-emerald-600');
                            copyBtn.classList.add('bg-slate-900');
                        }, 2000);
                    });
            });
        }

        // Auto-open modal if redirected from notification with parameters
        @if(request()->has('is_win'))
        (function() {
            const prizeName = @json(request('prize_name', ''));
            const couponCode = @json(request('coupon_code', ''));
            const isWin = {{ request('is_win') == '1' ? 'true' : 'false' }};
            
            // Wait for DOM to be fully loaded and open modal
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => openWinnerModal(prizeName, couponCode, isWin));
            } else {
                openWinnerModal(prizeName, couponCode, isWin);
            }
        })();
        @endif
    </script>
</x-app-layout>
