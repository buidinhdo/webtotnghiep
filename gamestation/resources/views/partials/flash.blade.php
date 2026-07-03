@if (session('success'))
    <div class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="gs-card border-l-4 border-emerald-400 bg-emerald-50/80 p-4 text-emerald-700">
            {{ session('success') }}
        </div>
    </div>
    @if (session('success') === 'Đặt hàng thành công.')
        <audio id="orderSuccessSound" src="https://tiengdong.com/wp-content/uploads/tieng-ting-ting-chuyen-khoan-www_tiengdong_com.mp3" preload="auto"></audio>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const audio = document.getElementById('orderSuccessSound');
                if (audio) {
                    audio.volume = 0.3; // Âm lượng nhẹ nhàng vừa phải
                    audio.play().catch(err => console.log('Audio playback blocked:', err));
                }
            });
        </script>
    @endif
@endif

@if (session('error'))
    <div class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="gs-card border-l-4 border-rose-400 bg-rose-50/80 p-4 text-rose-700">
            {{ session('error') }}
        </div>
    </div>
@endif
