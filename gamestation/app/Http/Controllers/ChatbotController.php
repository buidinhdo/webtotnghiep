<?php

namespace App\Http\Controllers;

use App\Models\ChatbotMessage;
use App\Models\Product;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getMessages(Request $request)
    {
        $messages = ChatbotMessage::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $user = $request->user();
        $userMessageText = trim($request->input('message'));

        // 1. Save user's message
        $userMessage = ChatbotMessage::create([
            'user_id' => $user->id,
            'sender' => 'user',
            'message' => $userMessageText,
        ]);

        $botReply = null;

        // Try using Google Gemini API first
        $apiKey = config('services.gemini.key');
        if ($apiKey) {
            try {
                $storeAddress = \App\Models\Setting::get('store_address', config('shipping.shop_address', 'Hà Nội'));
                $storePhone = \App\Models\Setting::get('store_phone', '0123456789');

                $products = Product::where('is_active', true)
                    ->select('id', 'name', 'price', 'stock', 'platform', 'genre', 'slug')
                    ->take(150)
                    ->get();

                $catalog = "";
                foreach ($products as $p) {
                    $url = route('products.show', $p);
                    $catalog .= "- Tên: {$p->name} | Hệ máy: " . strtoupper($p->platform ?? 'N/A') . " | Giá: " . number_format($p->price, 0, ',', '.') . "đ | Tồn kho: {$p->stock} | Thể loại: {$p->genre} | Link chi tiết: {$url}\n";
                }

                $systemInstruction = "Bạn là trợ lý ảo AI thông minh và thân thiện của cửa hàng GameStation.
Nhiệm vụ của bạn là trả lời các câu hỏi của khách hàng về sản phẩm, chính sách của shop một cách tự nhiên, lịch sự bằng tiếng Việt.
Hãy xưng hô là 'Shop' và gọi khách hàng là 'bạn'.
Địa chỉ cửa hàng: {$storeAddress}. Số điện thoại: {$storePhone}.

Dưới đây là danh sách sản phẩm thực tế của cửa hàng kèm đường link chi tiết. Khi trả lời hoặc gợi ý sản phẩm, hãy LUÔN cung cấp link chi tiết (sử dụng định dạng markdown: [Tên game](URL_Link) để khách bấm vào xem được):
{$catalog}

Hãy trả lời ngắn gọn, tập trung vào câu hỏi của khách. Không bịa đặt thông tin không có trong danh sách.";

                // Get conversation history (last 10 messages)
                $history = ChatbotMessage::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get()
                    ->reverse();

                $payloadContents = [];
                $lastRole = null;
                foreach ($history as $h) {
                    $role = $h->sender === 'user' ? 'user' : 'model';
                    if ($role === $lastRole) {
                        continue;
                    }
                    $payloadContents[] = [
                        'role' => $role,
                        'parts' => [
                            ['text' => $h->message]
                        ]
                    ];
                    $lastRole = $role;
                }

                if (!empty($payloadContents) && $payloadContents[0]['role'] !== 'user') {
                    array_shift($payloadContents);
                }

                if (empty($payloadContents)) {
                    $payloadContents[] = [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $userMessageText]
                        ]
                    ];
                }

                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key={$apiKey}", [
                    'contents' => $payloadContents,
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $systemInstruction]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $botReply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gemini API Error: " . $e->getMessage());
            }
        }

        // If Gemini API fails or is not configured, fallback to rule-based chatbot logic
        if (!$botReply) {
            $lowerMessage = mb_strtolower($userMessageText);

            // 2. Detect query intents for attributes
            $requestedAttributes = [];
            if (preg_match('/(?:giá|bao nhiêu|nhiêu|tiền|cost|price|how\s*much)/u', $lowerMessage)) {
                $requestedAttributes['price'] = 'Giá';
            }
            if (preg_match('/(?:số lượng|còn hàng|hết hàng|còn không|tình trạng|tồn|kho|sẵn hàng|stock|quantity)/u', $lowerMessage)) {
                $requestedAttributes['stock'] = 'Tình trạng';
            }
            if (preg_match('/(?:hệ máy|thiết bị|chơi trên|chơi bằng|platform|máy)/u', $lowerMessage)) {
                $requestedAttributes['platform'] = 'Hệ máy';
            }
            if (preg_match('/(?:nhà phát hành|nhà sản xuất|hãng|publisher|sản xuất|phát hành)/u', $lowerMessage)) {
                $requestedAttributes['publisher'] = 'Nhà phát hành';
            }
            if (preg_match('/(?:bảo hành|bh)/u', $lowerMessage)) {
                $requestedAttributes['warranty'] = 'Bảo hành';
            }
            if (preg_match('/(?:phiên bản|dung lượng|gb|bản|version|capacity)/u', $lowerMessage)) {
                $requestedAttributes['version'] = 'Phiên bản/Dung lượng';
            }
            if (preg_match('/(?:thể loại|dòng game|genre)/u', $lowerMessage)) {
                $requestedAttributes['genre'] = 'Thể loại';
            }
            if (preg_match('/(?:ngày phát hành|ngày ra mắt|ra mắt khi nào|phát hành khi nào|ngày bán|release\s*date)/u', $lowerMessage)) {
                $requestedAttributes['release_date'] = 'Ngày phát hành';
            }

            $showFullInfo = false;
            if (str_contains($lowerMessage, 'toàn bộ thông tin') || str_contains($lowerMessage, 'tất cả thông tin') || str_contains($lowerMessage, 'thông tin đầy đủ') || str_contains($lowerMessage, 'full thông tin')) {
                $showFullInfo = true;
            }

            // 3. Detect Filter/Search Query
            $isPlatformFilter = false;
            $filterPlatform = null;
            if (preg_match('/(?:dành cho|cho|hệ máy|hệ)\s*(ps5|ps4|switch|nintendo switch|nintendo|playstation)/u', $lowerMessage, $matches)) {
                $isPlatformFilter = true;
                $filterPlatform = trim($matches[1]);
                if ($filterPlatform === 'nintendo') {
                    $filterPlatform = 'switch';
                }
            }

            $isPriceFilter = false;
            $filterPriceMax = null;
            $filterPriceMin = null;
            if (preg_match('/(?:dưới|thấp hơn|nhỏ hơn|<=|<)\s*([\d\.,\s]+)\s*(triệu|tr|k|đ|vnd|đồng)?/u', $lowerMessage, $matches)) {
                $isPriceFilter = true;
                $valStr = preg_replace('/[^\d]/', '', $matches[1]);
                $val = (float)$valStr;
                $unit = isset($matches[2]) ? trim($matches[2]) : '';
                if (str_contains($unit, 'triệu') || str_contains($unit, 'tr')) {
                    $val *= 1000000;
                } elseif (str_contains($unit, 'k')) {
                    $val *= 1000;
                } elseif ($val < 1000) {
                    if ($val < 10) {
                        $val *= 1000000;
                    } else {
                        $val *= 1000;
                    }
                }
                $filterPriceMax = $val;
            } elseif (preg_match('/(?:trên|cao hơn|lớn hơn|>=|>)\s*([\d\.,\s]+)\s*(triệu|tr|k|đ|vnd|đồng)?/u', $lowerMessage, $matches)) {
                $isPriceFilter = true;
                $valStr = preg_replace('/[^\d]/', '', $matches[1]);
                $val = (float)$valStr;
                $unit = isset($matches[2]) ? trim($matches[2]) : '';
                if (str_contains($unit, 'triệu') || str_contains($unit, 'tr')) {
                    $val *= 1000000;
                } elseif (str_contains($unit, 'k')) {
                    $val *= 1000;
                } elseif ($val < 1000) {
                    if ($val < 10) {
                        $val *= 1000000;
                    } else {
                        $val *= 1000;
                    }
                }
                $filterPriceMin = $val;
            }

            $isGenreFilter = false;
            $filterGenre = null;
            $genres = ['đua xe', 'hành động', 'nhập vai', 'bắn súng', 'thể thao', 'phiêu lưu', 'kinh dị', 'đối kháng', 'chiến thuật', 'fps', 'rpg', 'moba'];
            foreach ($genres as $genre) {
                if (str_contains($lowerMessage, $genre)) {
                    $isGenreFilter = true;
                    $filterGenre = $genre;
                    break;
                }
            }

            // Clean user message for product keywords search
            $cleanString = preg_replace('/[^\p{L}\p{N}\s]/u', '', $lowerMessage);
            $tokens = array_filter(explode(' ', $cleanString));
            
            $stopWords = [
                'có', 'không', 'tìm', 'mua', 'bán', 'game', 'nút', 'thế', 'nào', 'tư', 'vấn', 
                'cho', 'hỏi', 'tôi', 'ở', 'đâu', 'shop', 'cửa', 'hàng', 'admin', 'ad', 'ơi', 
                'nhỉ', 'với', 'cần', 'muốn', 'hiện', 'tại', 'bên', 'mình', 'web', 'sản', 'phẩm', 
                'đĩa', 'máy', 'được', 'lấy', 'cho', 'ra', 'sao', 'này', 'kia', 'đó', 'ạ', 'ko', 'kg',
                'giá', 'bao', 'nhiêu', 'tiền', 'của', 'là', 'về', 'cái', 'nhé', 'nha', 'được', 
                'hộ', 'giúp', 'xin', 'báo', 'xem', 'biết', 'các', 'những', 'một', 'số', 'bản', 'hệ',
                'tình', 'trạng', 'còn', 'hết', 'tin', 'tức', 'mô', 'tả', 'hệ', 'máy', 'nhà', 'phát', 'hành',
                'thể', 'loại', 'ngày', 'ra', 'mắt', 'bảo', 'hành', 'dung', 'lượng', 'phiên', 'bản',
                'dưới', 'trên', 'khoảng', 'triệu', 'đồng', 'tấn', 'loại'
            ];

            $keywords = array_filter($tokens, function ($token) use ($stopWords) {
                return !in_array($token, $stopWords) && mb_strlen($token) >= 2;
            });

            $matchedProducts = collect();
            if (!empty($keywords)) {
                $productsQuery = Product::with(['primaryImage', 'images', 'publisher'])
                    ->where('is_active', true);
                
                foreach ($keywords as $keyword) {
                    $productsQuery->where(function ($builder) use ($keyword) {
                        $builder->where('name', 'like', "%{$keyword}%")
                                ->orWhere('short_description', 'like', "%{$keyword}%");
                    });
                }
                
                $matchedProducts = $productsQuery->take(10)->get();
            }

            // Determine context product
            $currentProduct = null;

            if ($matchedProducts->count() === 1) {
                $currentProduct = $matchedProducts->first();
                session(['chatbot_current_product_id' => $currentProduct->id]);
            } elseif ($matchedProducts->count() > 1) {
                $botReply = "Tôi tìm thấy một số sản phẩm liên quan đến yêu cầu của bạn:\n\n";
                foreach ($matchedProducts as $index => $prod) {
                    $url = route('products.show', $prod);
                    $botReply .= ($index + 1) . ". **{$prod->name}** (" . strtoupper($prod->platform ?? 'N/A') . ") - [Xem chi tiết]({$url})\n";
                }
                $botReply .= "\nBạn đang cần tư vấn cụ thể về sản phẩm nào dưới đây? Vui lòng cho tôi biết nhé!";

                $botMessage = ChatbotMessage::create([
                    'user_id' => $user->id,
                    'sender' => 'bot',
                    'message' => $botReply,
                ]);

                return response()->json([
                    'success' => true,
                    'user_message' => $userMessage,
                    'bot_message' => $botMessage,
                ]);
            }

            if (!$currentProduct) {
                $currentProduct = $this->getLastDiscussedProduct($user->id);
            }

            if ($currentProduct) {
                if ($showFullInfo) {
                    $priceFormatted = number_format($currentProduct->price, 0, ',', '.') . 'đ';
                    $statusText = $currentProduct->stock > 0 ? "Còn hàng (Số lượng còn: {$currentProduct->stock} sản phẩm)" : "Hết hàng";
                    $platform = strtoupper($currentProduct->platform ?? 'N/A');
                    $pubName = $currentProduct->publisher ? $currentProduct->publisher->name : 'Chưa có thông tin';
                    $genre = $currentProduct->genre ?: 'Chưa có thông tin';
                    $releaseDate = $currentProduct->release_date ? date('d/m/Y', strtotime($currentProduct->release_date)) : 'Chưa có thông tin';
                    $desc = $currentProduct->short_description ?: ($currentProduct->description ?: 'Không có mô tả.');
                    $url = route('products.show', $currentProduct);

                    $botReply = "Thông tin đầy đủ của sản phẩm **{$currentProduct->name}**:\n\n";
                    $botReply .= "🔹 Giá: **{$priceFormatted}**\n";
                    $botReply .= "🔹 Hệ máy: **{$platform}**\n";
                    $botReply .= "🔹 Tình trạng: **{$statusText}**\n";
                    $botReply .= "🔹 Nhà phát hành: **{$pubName}**\n";
                    $botReply .= "🔹 Thể loại: **{$genre}**\n";
                    $botReply .= "🔹 Ngày phát hành: **{$releaseDate}**\n";
                    $botReply .= "🔹 Mô tả ngắn: " . \Illuminate\Support\Str::limit($desc, 250) . "\n\n";
                    $botReply .= "🔗 [Xem chi tiết sản phẩm]({$url})";
                } elseif (!empty($requestedAttributes)) {
                    $replies = [];
                    foreach ($requestedAttributes as $key => $label) {
                        if ($key === 'price') {
                            $priceFormatted = number_format($currentProduct->price, 0, ',', '.') . 'đ';
                            $replies[] = "🔹 **Giá**: {$priceFormatted}";
                        }
                        if ($key === 'stock') {
                            $statusText = $currentProduct->stock > 0 ? "Còn hàng (Số lượng còn: {$currentProduct->stock})" : "Hết hàng";
                            $replies[] = "🔹 **Tình trạng**: {$statusText}";
                        }
                        if ($key === 'platform') {
                            $platform = strtoupper($currentProduct->platform ?? 'N/A');
                            $replies[] = "🔹 **Hệ máy**: {$platform}";
                        }
                        if ($key === 'publisher') {
                            $pubName = $currentProduct->publisher ? $currentProduct->publisher->name : 'Chưa có thông tin';
                            $replies[] = "🔹 **Nhà phát hành**: {$pubName}";
                        }
                        if ($key === 'warranty') {
                            $replies[] = "🔹 **Bảo hành**: Chưa có thông tin";
                        }
                        if ($key === 'version') {
                            $replies[] = "🔹 **Phiên bản/Dung lượng**: Chưa có thông tin";
                        }
                        if ($key === 'genre') {
                            $genre = $currentProduct->genre ?: 'Chưa có thông tin';
                            $replies[] = "🔹 **Thể loại**: {$genre}";
                        }
                        if ($key === 'release_date') {
                            $releaseDate = $currentProduct->release_date ? date('d/m/Y', strtotime($currentProduct->release_date)) : 'Chưa có thông tin';
                            $replies[] = "🔹 **Ngày phát hành**: {$releaseDate}";
                        }
                    }
                    $botReply = "Thông tin bạn hỏi về sản phẩm **{$currentProduct->name}**:\n\n" . implode("\n", $replies);
                } else {
                    $priceFormatted = number_format($currentProduct->price, 0, ',', '.') . 'đ';
                    $statusText = $currentProduct->stock > 0 ? "Còn hàng" : "Hết hàng";
                    $platform = strtoupper($currentProduct->platform ?? 'N/A');
                    $url = route('products.show', $currentProduct);

                    $botReply = "Chào bạn! Đây là thông tin tổng quan của game **{$currentProduct->name}**:\n\n";
                    $botReply .= "🔹 Giá: **{$priceFormatted}**\n";
                    $botReply .= "🔹 Hệ máy: **{$platform}**\n";
                    $botReply .= "🔹 Tình trạng: **{$statusText}**\n\n";
                    $botReply .= "Bạn có cần tư vấn chi tiết hơn về các thuộc tính như số lượng, nhà phát hành, hay thể loại của game này không?\n";
                    $botReply .= "🔗 [Xem chi tiết sản phẩm]({$url})";
                }
            } else {
                if ($isPlatformFilter || $isPriceFilter || $isGenreFilter) {
                    $filterQuery = Product::with(['primaryImage', 'images', 'publisher'])->where('is_active', true);
                    $filterDesc = [];

                    if ($isPlatformFilter && $filterPlatform) {
                        $filterQuery->where('platform', 'like', "%{$filterPlatform}%");
                        $filterDesc[] = "hệ máy " . strtoupper($filterPlatform);
                    }

                    if ($isPriceFilter) {
                        if ($filterPriceMax !== null) {
                            $filterQuery->where('price', '<=', $filterPriceMax);
                            $filterDesc[] = "giá dưới " . number_format($filterPriceMax, 0, ',', '.') . "đ";
                        }
                        if ($filterPriceMin !== null) {
                            $filterQuery->where('price', '>=', $filterPriceMin);
                            $filterDesc[] = "giá trên " . number_format($filterPriceMin, 0, ',', '.') . "đ";
                        }
                    }

                    if ($isGenreFilter && $filterGenre) {
                        $filterQuery->where(function ($q) use ($filterGenre) {
                            $q->where('genre', 'like', "%{$filterGenre}%")
                              ->orWhere('name', 'like', "%{$filterGenre}%")
                              ->orWhere('short_description', 'like', "%{$filterGenre}%")
                              ->orWhere('description', 'like', "%{$filterGenre}%");
                        });
                        $filterDesc[] = "thể loại " . $filterGenre;
                    }

                    $filteredProducts = $filterQuery->take(5)->get();
                    if ($filteredProducts->isNotEmpty()) {
                        $descString = implode(', ', $filterDesc);
                        $botReply = "Chào bạn! Tôi tìm thấy một số sản phẩm phù hợp với yêu cầu ({$descString}) của bạn:\n\n";
                        foreach ($filteredProducts as $prod) {
                            $priceFormatted = number_format($prod->price, 0, ',', '.') . 'đ';
                            $url = route('products.show', $prod);
                            $botReply .= "🔹 **{$prod->name}**\n";
                            $botReply .= "   Platform: " . strtoupper($prod->platform ?? 'N/A') . "\n";
                            $botReply .= "   Giá: {$priceFormatted}\n";
                            $botReply .= "   🔗 [Xem chi tiết sản phẩm](" . $url . ")\n\n";
                        }
                    } else {
                        $botReply = "Hiện không tìm thấy sản phẩm phù hợp với yêu cầu của bạn.";
                    }
                } else {
                    $botReply = "Hiện tại tôi chưa rõ bạn đang cần tư vấn về sản phẩm hay chủ đề nào. Bạn có thể cho tôi biết tên game hoặc thông tin cụ thể bạn cần hỏi không?";
                }
            }
        }

        // 6. Save bot's message
        $botMessage = ChatbotMessage::create([
            'user_id' => $user->id,
            'sender' => 'bot',
            'message' => $botReply,
        ]);

        return response()->json([
            'success' => true,
            'user_message' => $userMessage,
            'bot_message' => $botMessage,
        ]);
    }

    private function getLastDiscussedProduct($userId)
    {
        // Try session first
        if (session()->has('chatbot_current_product_id')) {
            $productId = session('chatbot_current_product_id');
            $product = Product::with('publisher')->find($productId);
            if ($product && $product->is_active) {
                return $product;
            }
        }

        // Fallback to database history (scan last 15 messages)
        $messages = ChatbotMessage::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        foreach ($messages as $msg) {
            if (preg_match('/\/products\/([a-z0-9\-]+)/', $msg->message, $matches)) {
                $slug = $matches[1];
                $product = Product::with('publisher')
                    ->where('slug', $slug)
                    ->orWhere('id', $slug)
                    ->first();
                
                if ($product && $product->is_active) {
                    session(['chatbot_current_product_id' => $product->id]);
                    return $product;
                }
            }
        }

        return null;
    }
}
