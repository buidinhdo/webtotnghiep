<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700|ibm-plex-serif:400,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Temporary override removed; using compiled CSS in public/build/assets/app-*.css -->
    </head>
    <body class="antialiased">
        <div class="min-h-screen">
            @include('layouts.navigation')

            @auth
                @php
                    $hasSpunToday = \App\Models\UserNotification::where('user_id', auth()->id())
                        ->where('title', 'Vòng quay may mắn')
                        ->where('created_at', '>=', \Carbon\Carbon::today())
                        ->exists();
                @endphp
                @if(!$hasSpunToday)
                    <!-- Premium Lucky Spin Alert Bar -->
                    <div class="bg-gradient-to-r from-violet-600 via-indigo-600 to-sky-600 text-white py-3 px-4 shadow-md text-sm font-semibold tracking-wide relative overflow-hidden" x-data="{ showBar: true }" x-show="showBar" style="transform: translate3d(0, 0, 0); backface-visibility: hidden; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
                        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 relative z-10">
                            <div class="flex items-center gap-2 text-center sm:text-left">
                                <span class="text-lg inline-block animate-bounce" style="will-change: transform;">🎁</span>
                                <span>Bạn có <span class="text-amber-300 font-bold">1 lượt quay may mắn</span> chưa sử dụng hôm nay! Hãy thử vận may nhận mã giảm giá lên tới 500k.</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('lucky-spin.index') }}" class="bg-white hover:bg-slate-50 text-indigo-700 px-4 py-1.5 rounded-full text-xs font-extrabold tracking-wide uppercase transition shadow hover:scale-105 no-underline">
                                    Quay ngay
                                </a>
                                <button @click="showBar = false" class="text-white/70 hover:text-white transition-colors p-1" aria-label="Đóng thông báo">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endauth

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            @include('partials.flash')

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            @include('layouts.footer')
        </div>

        @auth
            <!-- Floating Chatbot Widget -->
            <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end" x-data="chatbotComponent()">
                <!-- Chat Box -->
                <div x-show="chatOpen" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                     class="mb-4 w-96 max-w-[calc(100vw-2rem)] h-[500px] bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden text-white"
                     style="display: none;">
                    <!-- Header -->
                    <div class="px-4 py-3 bg-gradient-to-r from-sky-600 to-indigo-600 flex items-center justify-between border-b border-slate-800">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center font-bold text-sm">🤖</div>
                            <div>
                                <h4 class="font-bold text-sm">GameStation Assistant</h4>
                                <span class="text-[10px] text-sky-200">Online &bull; Tự động trả lời</span>
                            </div>
                        </div>
                        <button @click="chatOpen = false" class="text-white/80 hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Messages list -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-950" x-ref="messageContainer">
                        <!-- Welcome Message if empty -->
                        <template x-if="messages.length === 0">
                            <div class="flex items-start gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-sky-600/30 flex items-center justify-center text-xs">🤖</div>
                                <div class="bg-slate-800 text-slate-200 p-3 rounded-2xl rounded-tl-none max-w-[85%] text-xs leading-relaxed">
                                    Chào bạn! Tôi là trợ lý ảo của **GameStation**. Bạn muốn tìm mua sản phẩm nào? Hãy nhập tên sản phẩm hoặc nhu cầu của bạn vào ô chat bên dưới nhé!
                                </div>
                            </div>
                        </template>

                        <template x-for="msg in messages" :key="msg.id || msg.created_at">
                            <div :class="msg.sender === 'user' ? 'flex items-start justify-end gap-2.5' : 'flex items-start gap-2.5'">
                                <template x-if="msg.sender !== 'user'">
                                    <div :class="msg.sender === 'admin' ? 'w-7 h-7 rounded-full bg-emerald-600/30 flex items-center justify-center text-xs border border-emerald-500' : 'w-7 h-7 rounded-full bg-sky-600/30 flex items-center justify-center text-xs border border-sky-500'">
                                        <span x-text="msg.sender === 'admin' ? '👨‍💻' : '🤖'"></span>
                                    </div>
                                </template>
                                <div :class="msg.sender === 'user' ? 'bg-sky-600 text-white p-3 rounded-2xl rounded-tr-none max-w-[85%] text-xs leading-relaxed shadow-md shadow-sky-600/10' : (msg.sender === 'admin' ? 'bg-slate-800 text-slate-100 p-3 border border-slate-700 rounded-2xl rounded-tl-none max-w-[85%] text-xs leading-relaxed' : 'bg-slate-900 text-slate-200 p-3 border border-slate-800 rounded-2xl rounded-tl-none max-w-[85%] text-xs leading-relaxed')">
                                    <!-- Sender name if Admin -->
                                    <template x-if="msg.sender === 'admin'">
                                        <span class="block font-bold text-[10px] text-emerald-400 mb-1">Quản trị viên:</span>
                                    </template>
                                    <p x-html="formatMessage(msg.message)"></p>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Loading indicator -->
                        <div x-show="loading" class="flex items-start gap-2.5" style="display: none;">
                            <div class="w-7 h-7 rounded-full bg-sky-600/30 flex items-center justify-center text-xs">🤖</div>
                            <div class="bg-slate-900 text-slate-400 p-3 rounded-2xl rounded-tl-none max-w-[85%] text-xs leading-relaxed flex items-center gap-1">
                                <span class="w-1.5 h-1.5 bg-slate-500 rounded-full animate-bounce"></span>
                                <span class="w-1.5 h-1.5 bg-slate-500 rounded-full animate-bounce [animation-delay:0.2s]"></span>
                                <span class="w-1.5 h-1.5 bg-slate-500 rounded-full animate-bounce [animation-delay:0.4s]"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Input area -->
                    <form @submit.prevent="sendMessage()" class="p-3 bg-slate-900 border-t border-slate-800 flex gap-2 items-center">
                        <input type="text" x-model="userMessage" placeholder="Nhập tin nhắn..." class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-sky-500 text-white placeholder-slate-500" required>
                        
                        <!-- Mic Button -->
                        <button type="button" @click="toggleSpeechRecognition()" :class="isListening ? 'bg-red-600 hover:bg-red-500 animate-pulse' : 'bg-slate-800 hover:bg-slate-700'" class="transition p-2 rounded-xl flex items-center justify-center text-white" title="Nói để nhập văn bản">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"></path>
                            </svg>
                        </button>

                        <button type="submit" class="bg-sky-600 hover:bg-sky-500 transition px-3 py-2 rounded-xl flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        </button>
                    </form>
                </div>

                <!-- Chat Bubble Button -->
                <button @click="chatOpen = !chatOpen; scrollToBottom();" class="w-14 h-14 rounded-full bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 transition shadow-2xl flex items-center justify-center text-white relative hover:scale-105 transform duration-300">
                    <svg x-show="!chatOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    <svg x-show="chatOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('chatbotComponent', () => ({
                        chatOpen: false,
                        messages: [],
                        userMessage: '',
                        loading: false,
                        pollingInterval: null,
                        isListening: false,
                        recognition: null,
                        toggleSpeechRecognition() {
                            if (this.isListening) {
                                if (this.recognition) {
                                    this.recognition.stop();
                                }
                                this.isListening = false;
                                return;
                            }

                            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                            if (!SpeechRecognition) {
                                alert("Trình duyệt của bạn không hỗ trợ nhận diện giọng nói. Hãy dùng Google Chrome hoặc Microsoft Edge.");
                                return;
                            }

                            if (!this.recognition) {
                                this.recognition = new SpeechRecognition();
                                this.recognition.lang = 'vi-VN';
                                this.recognition.continuous = false;
                                this.recognition.interimResults = false;

                                this.recognition.onstart = () => {
                                    this.isListening = true;
                                };

                                this.recognition.onerror = (event) => {
                                    console.error("Speech recognition error:", event.error);
                                    this.isListening = false;
                                };

                                this.recognition.onend = () => {
                                    this.isListening = false;
                                };

                                 this.recognition.onresult = (event) => {
                                     let transcript = event.results[0][0].transcript;
                                     if (transcript) {
                                         // Auto correct common speech-to-text homophone errors
                                         transcript = transcript.replace(/\b4\s*(vấn|van)\b/gi, 'tư vấn');
                                         this.userMessage = transcript;
                                         this.sendMessage();
                                     }
                                 };
                            }

                            this.recognition.start();
                        },
                        init() {
                            this.fetchMessages();
                            // Start polling every 5 seconds to load admin responses
                            this.pollingInterval = setInterval(() => {
                                if (this.chatOpen) {
                                    this.fetchMessages();
                                }
                            }, 5000);
                        },
                        fetchMessages() {
                            fetch('{{ route('chatbot.messages') }}')
                                .then(res => res.json())
                                .then(data => {
                                    const oldLength = this.messages.length;
                                    this.messages = data;
                                    if (data.length > oldLength) {
                                        this.scrollToBottom();
                                    }
                                });
                        },
                        sendMessage() {
                            if (!this.userMessage.trim()) return;
                            const messageText = this.userMessage;
                            this.userMessage = '';
                            this.loading = true;
                            
                            // Optimistically add user message to list
                            this.messages.push({
                                sender: 'user',
                                message: messageText,
                                created_at: new Date().toISOString()
                            });
                            this.scrollToBottom();

                            fetch('{{ route('chatbot.send') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ message: messageText })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    this.fetchMessages();
                                }
                                this.loading = false;
                            })
                            .catch(() => {
                                this.loading = false;
                            });
                        },
                        scrollToBottom() {
                            this.$nextTick(() => {
                                const container = this.$refs.messageContainer;
                                if (container) {
                                    container.scrollTop = container.scrollHeight;
                                }
                            });
                        },
                        formatMessage(text) {
                            // Simple markdown-like formatting for images, links and bold text
                            let formatted = text
                                .replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;');

                            // Parse images first: ![alt](url) -> <img ...>
                            formatted = formatted.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" class="max-w-full rounded-lg mt-2 shadow-md border border-slate-700 max-h-48 object-cover">');

                            // Parse bold text
                            formatted = formatted.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

                            // Parse links
                            formatted = formatted.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-sky-400 hover:underline font-semibold" target="_blank">$1</a>');

                            // Parse newlines
                            formatted = formatted.replace(/\n/g, '<br>');

                            return formatted;
                        }
                    }));
                });
            </script>
        @endauth
    </body>

    {{-- Touch handler: hiện ảnh phụ khi chạm vào sản phẩm trên mobile --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('touchstart', function (e) {
                var imgEl = e.target.closest('.gs-product-image--hoverable');
                // Xóa class is-touched trên các card khác
                document.querySelectorAll('.gs-product-image--hoverable.is-touched').forEach(function (el) {
                    if (el !== imgEl) el.classList.remove('is-touched');
                });
                if (imgEl) {
                    imgEl.classList.toggle('is-touched');
                }
            }, { passive: true });
        });
    </script>
</html>
