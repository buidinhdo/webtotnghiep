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

                $products = Product::with(['publisher', 'category', 'images'])->where('is_active', true)->get();

                $catalog = "";
                foreach ($products as $p) {
                    $url = route('products.show', $p);
                    $publisherName = $p->publisher ? $p->publisher->name : 'Chưa cập nhật';
                    $categoryName = $p->category ? $p->category->name : 'Chưa cập nhật';
                    $releaseDate = $p->release_date ? date('d/m/Y', strtotime($p->release_date)) : 'Chưa cập nhật';
                    $sku = $p->sku ?? 'N/A';
                    $esrb = $p->esrb ?? 'Chưa phân loại';
                    $shortDesc = $p->short_description ? \Illuminate\Support\Str::limit($p->short_description, 80) : 'Chưa cập nhật';
                    $detailedSpecs = $p->detailed_description ? \Illuminate\Support\Str::limit(str_replace(["\r\n", "\r", "\n"], "; ", trim($p->detailed_description)), 200) : 'Chưa cập nhật';
                    
                    $mainImage = $p->images->first();
                    $imagePath = $mainImage ? asset($mainImage->image_path) : '';
                    
                    $catalog .= "- Tên: {$p->name} | Hệ máy: " . strtoupper($p->platform ?? 'N/A') . " | Danh mục: {$categoryName} | SKU: {$sku} | ESRB: {$esrb} | Giá: " . number_format($p->price, 0, ',', '.') . "đ | Tồn kho: {$p->stock} | Thể loại: {$p->genre} | Nhà phát hành: {$publisherName} | Ngày ra mắt: {$releaseDate} | Mô tả ngắn: {$shortDesc} | Thông số chi tiết: {$detailedSpecs} | Link chi tiết: {$url} | Đường dẫn hình ảnh: {$imagePath}\n";
                }

                // Detect specific order in user query
                $orderContext = "";
                $orderId = null;
                if (preg_match('/(?:đơn hàng|đơn số|mã đơn|đơn)\s*(?:số)?\s*#?(\d+)/u', mb_strtolower($userMessageText), $matches)) {
                    $orderId = $matches[1];
                }

                if ($orderId) {
                    $order = \App\Models\Order::with('items')
                        ->where('user_id', $user->id)
                        ->where('id', $orderId)
                        ->first();
                    
                    if ($order) {
                        $orderItemsStr = $order->items->map(fn($item) => $item->product_name . " (x" . $item->quantity . ")")->implode(', ');
                        $placedTime = $order->placed_at ? $order->placed_at->format('d/m/Y H:i:s') : $order->created_at->format('d/m/Y H:i:s');
                        
                        $orderContext = "THÔNG TIN CHI TIẾT ĐƠN HÀNG MÀ KHÁCH ĐANG HỎI TRỰC TIẾP:\n"
                            . "- Mã đơn hàng: #{$order->id}\n"
                            . "- Trạng thái đơn hàng: {$order->status_label}\n"
                            . "- Thời gian tạo/đặt hàng: {$placedTime}\n"
                            . "- Hình thức vận chuyển: " . ($order->shipping_method ?? 'Giao hàng tiêu chuẩn') . "\n"
                            . "- Khoảng cách vận chuyển từ shop đến địa chỉ nhận: " . number_format($order->shipping_distance_km, 2, ',', '.') . " km\n"
                            . "- Phí vận chuyển: " . number_format($order->shipping_fee, 0, ',', '.') . "đ\n"
                            . "- Phương thức thanh toán: {$order->payment_method_label}\n"
                            . "- Trạng thái thanh toán: {$order->payment_status_label}\n"
                            . "- Người nhận: {$order->shipping_name} | SĐT: {$order->shipping_phone}\n"
                            . "- Địa chỉ nhận hàng: {$order->shipping_address}\n"
                            . "- Danh sách sản phẩm trong đơn: {$orderItemsStr}\n"
                            . "- Tổng số tiền thanh toán: " . number_format($order->total, 0, ',', '.') . "đ\n";
                    } else {
                        $orderContext = "Hệ thống KHÔNG tìm thấy đơn hàng số #{$orderId} nào thuộc về tài khoản của khách hàng này.\n";
                    }
                } else {
                    // Load last 3 orders as general context
                    $recentOrders = \App\Models\Order::with('items')
                        ->where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->take(3)
                        ->get();
                        
                    if ($recentOrders->isNotEmpty()) {
                        $orderContext = "DANH SÁCH 3 ĐƠN HÀNG GẦN NHẤT CỦA KHÁCH HÀNG:\n";
                        foreach ($recentOrders as $ro) {
                            $items = $ro->items->map(fn($item) => $item->product_name . " (x" . $item->quantity . ")")->implode(', ');
                            $placedTime = $ro->placed_at ? $ro->placed_at->format('d/m/Y H:i') : $ro->created_at->format('d/m/Y H:i');
                            $orderContext .= "- Đơn #{$ro->id} | Ngày đặt: {$placedTime} | Trạng thái: {$ro->status_label} | Khoảng cách: " . number_format($ro->shipping_distance_km, 2, ',', '.') . " km | Phí ship: " . number_format($ro->shipping_fee, 0, ',', '.') . "đ | Vận chuyển: " . ($ro->shipping_method ?? 'Giao hàng tiêu chuẩn') . " | Tổng tiền: " . number_format($ro->total, 0, ',', '.') . "đ | Sản phẩm: [{$items}]\n";
                        }
                    }
                }
                // Detect specific product to load its reviews
                $reviewsContext = "";
                $targetProduct = null;
                $lowerMsg = mb_strtolower($userMessageText);
                $cleanMsg = preg_replace('/[^\p{L}\p{N}\s]/u', '', $lowerMsg);

                // Try to find product match in the user message by exact name
                foreach ($products as $p) {
                    $cleanName = preg_replace('/[^\p{L}\p{N}\s]/u', '', mb_strtolower($p->name));
                    if (str_contains($cleanMsg, $cleanName)) {
                        $targetProduct = $p;
                        break;
                    }
                }

                // If not matched, try matching based on the highest number of keyword overlaps
                if (!$targetProduct) {
                    $bestProduct = null;
                    $maxMatches = 0;
                    $stopWords = ['game', 'shop', 'của', 'trên', 'máy', 'cho', 'bản', 'đĩa', 'bán', 'tìm', 'mua', 'này'];
                    
                    foreach ($products as $p) {
                        $cleanName = preg_replace('/[^\p{L}\p{N}\s]/u', '', mb_strtolower($p->name));
                        $nameWords = array_filter(explode(' ', $cleanName), function($w) use ($stopWords) {
                            return mb_strlen($w) >= 2 && !in_array($w, $stopWords);
                        });
                        
                        $matchesCount = 0;
                        foreach ($nameWords as $nw) {
                            if (str_contains($cleanMsg, $nw)) {
                                $matchesCount++;
                            }
                        }
                        
                        if ($matchesCount > $maxMatches) {
                            $maxMatches = $matchesCount;
                            $bestProduct = $p;
                        }
                    }
                    
                    if ($maxMatches > 0) {
                        $targetProduct = $bestProduct;
                    }
                }

                // Fallback to the last discussed product
                if (!$targetProduct) {
                    $targetProduct = $this->getLastDiscussedProduct($user->id);
                }

                if ($targetProduct) {
                    // Save to session so we keep the context
                    session(['chatbot_current_product_id' => $targetProduct->id]);

                    // Eager load reviews for this product
                    $reviews = \App\Models\Review::with('user')
                        ->where('product_id', $targetProduct->id)
                        ->orderBy('created_at', 'desc')
                        ->take(10)
                        ->get();

                    $avgRating = \App\Models\Review::where('product_id', $targetProduct->id)->avg('rating') ?? 0;
                    $reviewsCount = \App\Models\Review::where('product_id', $targetProduct->id)->count();

                    $reviewsText = "";
                    foreach ($reviews as $rev) {
                        $reviewer = $rev->user ? $rev->user->name : 'Ẩn danh';
                        $comment = $rev->comment ?: 'Không có bình luận';
                        $adminReply = $rev->admin_reply ? " (Phản hồi của admin: {$rev->admin_reply})" : "";
                        $reviewsText .= "- Khách hàng: {$reviewer} | Đánh giá: {$rev->rating}/5 sao | Nhận xét: {$comment}{$adminReply}\n";
                    }

                    $reviewsContext = "ĐÁNH GIÁ THỰC TẾ TỪ NGƯỜI MUA TRƯỚC VỀ SẢN PHẨM '{$targetProduct->name}':\n"
                        . "- Điểm đánh giá trung bình: " . round($avgRating, 1) . "/5 sao (Dựa trên {$reviewsCount} lượt đánh giá)\n"
                        . (!empty($reviewsText) ? "Danh sách nhận xét chi tiết:\n{$reviewsText}" : "Sản phẩm này chưa có lượt nhận xét viết bằng lời nào.\n");
                }
                // Fetch the user's current cart details
                $cartContext = "";
                $cart = \App\Models\Cart::with('items.product')->where('user_id', $user->id)->first();
                if ($cart && $cart->items->isNotEmpty()) {
                    $cartItemsStr = $cart->items->map(function ($item) {
                        if ($item->product) {
                            return $item->product->name . " (Hệ máy: " . strtoupper($item->product->platform ?? 'N/A') . ", Giá: " . number_format($item->product->price, 0, ',', '.') . "đ, SL: {$item->quantity})";
                        }
                        return "Sản phẩm không xác định (SL: {$item->quantity})";
                    })->implode(', ');
                    
                    $cartContext = "GIỎ HÀNG HIỆN TẠI CỦA KHÁCH HÀNG:\n"
                        . "- Các sản phẩm đang có trong giỏ hàng: [{$cartItemsStr}]\n"
                        . "- Tổng trị giá giỏ hàng tạm tính: " . number_format($cart->items->sum(fn($item) => $item->price * $item->quantity), 0, ',', '.') . "đ\n";
                } else {
                    $cartContext = "Giỏ hàng hiện tại của khách hàng đang trống.\n";
                }

                // Detect pre-purchase shipping estimation request
                $shippingEstimateContext = "";
                if (str_contains($lowerMsg, 'ship') || str_contains($lowerMsg, 'giao hàng') || str_contains($lowerMsg, 'khoảng cách') || str_contains($lowerMsg, 'km')) {
                    $addressCandidate = null;
                    if (preg_match('/(?:đến|tới|ở|tại|về)\s+([^?.\n\(\),]+(?:,\s*[^?.\n\(\),]+)*)/u', $userMessageText, $matches)) {
                        $addressCandidate = trim($matches[1]);
                    }
                    
                    if (!$addressCandidate || mb_strlen($addressCandidate) < 2) {
                        $provinces = ['hồ chí minh', 'hcm', 'sài gòn', 'hà nội', 'đà nẵng', 'hải phòng', 'cần thơ', 'nha trang', 'bình dương', 'đồng nai', 'vũng tàu', 'long an', 'tiền giang', 'bến tre', 'vĩnh long', 'trà vinh', 'hậu giang', 'sóc trăng', 'bạc liêu', 'cà mau', 'kiên giang', 'an giang', 'đồng tháp', 'tây ninh', 'bình phước', 'lâm đồng', 'đà lạt', 'đắk lắk', 'đắk nông', 'gia lai', 'kon tum', 'bình thuận', 'ninh thuận', 'khánh hòa', 'phú yên', 'bình định', 'quảng ngãi', 'quảng nam', 'thừa thiên huế', 'huế', 'quảng trị', 'quảng bình', 'hà tĩnh', 'nghệ an', 'thanh hóa', 'ninh bình', 'nam định', 'thái bình', 'hà nam', 'hưng yên', 'hải dương', 'bắc ninh', 'vĩnh phúc', 'phú thọ', 'quảng ninh', 'lạng sơn', 'cao bằng', 'hà giang', 'tuyên quang', 'bắc kạn', 'thái nguyên', 'lào cai', 'yên bái', 'điện biên', 'lai châu', 'sơn la', 'hòa bình'];
                        foreach ($provinces as $prov) {
                            if (str_contains($lowerMsg, $prov)) {
                                $addressCandidate = $prov;
                                break;
                            }
                        }
                    }

                    if ($addressCandidate && mb_strlen($addressCandidate) >= 2) {
                        $shop = $this->resolveShopLocation();
                        $customer = $this->geocodeAddress($addressCandidate);
                        if (!empty($shop) && !empty($customer)) {
                            $distanceKm = $this->resolveDistanceKm($shop, $customer);
                            $region = $this->resolveRegion($addressCandidate);
                            $estimatedFee = match ($region) {
                                'local' => 14750.0,
                                'south' => 25000.0,
                                'central' => 45250.0,
                                'north' => 55500.0,
                                default => 45250.0,
                            };
                            if (str_contains($lowerMsg, 'huyện') || str_contains($lowerMsg, 'xã')) {
                                $estimatedFee += 5250.0;
                            } else {
                                $estimatedFee += 1500.0;
                            }

                            $shippingEstimateContext = "DỰ TOÁN VẬN CHUYỂN TẠM TÍNH ĐẾN '{$addressCandidate}':\n"
                                . "- Địa chỉ/Khu vực khách hàng hỏi: {$addressCandidate}\n"
                                . "- Khoảng cách vận chuyển thực tế dự kiến: " . number_format($distanceKm, 2, ',', '.') . " km\n"
                                . "- Phí giao hàng tiêu chuẩn dự kiến: " . number_format($estimatedFee, 0, ',', '.') . "đ\n"
                                . "- Phí giao hàng hỏa tốc dự kiến: " . number_format($estimatedFee + ($region === 'local' ? 10000.0 : 25000.0), 0, ',', '.') . "đ\n";
                        }
                    }
                }

                $systemInstruction = "Bạn là trợ lý ảo AI thông minh và thân thiện của cửa hàng GameStation.
Nhiệm vụ của bạn là trả lời các câu hỏi của khách hàng về sản phẩm, chính sách của shop một cách tự nhiên, lịch sự bằng tiếng Việt.
Hãy xưng hô là 'Shop' và gọi khách hàng là 'bạn'.
Địa chỉ cửa hàng: {$storeAddress}. Số điện thoại: {$storePhone}.

Dưới đây là danh sách sản phẩm thực tế của cửa hàng kèm đường link chi tiết và thông số chi tiết:
{$catalog}

" . (!empty($orderContext) ? "Dữ liệu đơn hàng của khách hàng hiện tại:\n{$orderContext}\n" : "") . "
" . (!empty($reviewsContext) ? "Dữ liệu đánh giá của sản phẩm đang được nói đến:\n{$reviewsContext}\n" : "") . "
" . (!empty($cartContext) ? "Dữ liệu giỏ hàng của khách hàng hiện tại:\n{$cartContext}\n" : "") . "
" . (!empty($shippingEstimateContext) ? "Dữ liệu dự toán vận chuyển và khoảng cách tạm tính cho khách hàng:\n{$shippingEstimateContext}\n" : "") . "
Hãy trả lời ngắn gọn, tập trung vào câu hỏi của khách. Đối với câu hỏi về sản phẩm, hãy LUÔN cung cấp link chi tiết dưới dạng markdown (ví dụ: [Tên game](URL_Link)). Đặc biệt, nếu khách hàng hỏi xin xem ảnh sản phẩm hoặc muốn biết hình ảnh sản phẩm trông thế nào, bạn hãy sử dụng 'Đường dẫn hình ảnh' được cung cấp cho sản phẩm đó và chèn hình ảnh đó trực tiếp dưới dạng markdown: `![Tên game](Đường dẫn hình ảnh)`. Đối với câu hỏi về đơn hàng, hãy sử dụng dữ liệu đơn hàng được cung cấp ở trên để trả lời đầy đủ, chi tiết, chính xác các thông tin như tình trạng đơn hàng, thời gian, hình thức vận chuyển, phí ship, khoảng cách vận chuyển... Không bịa đặt thông tin không có trong danh sách.
Đối với câu hỏi về nhận xét hoặc đánh giá của sản phẩm, hãy sử dụng 'Dữ liệu đánh giá của sản phẩm đang được nói đến' ở trên để tóm tắt điểm đánh giá trung bình và các nhận xét cụ thể của khách hàng một cách trung thực, khách quan.
Đối với câu hỏi về giỏ hàng (ví dụ: giỏ hàng có gì, tổng tiền bao nhiêu, gợi ý game phù hợp với giỏ hàng...), hãy sử dụng 'Dữ liệu giỏ hàng của khách hàng hiện tại' ở trên để trả lời chi tiết và đề xuất các game tương tự hoặc phù hợp trên cùng hệ máy để khách mua thêm.
Đối với câu hỏi về phí ship và khoảng cách giao hàng tạm tính đến một địa điểm, hãy sử dụng 'Dữ liệu dự toán vận chuyển và khoảng cách tạm tính cho khách hàng' ở trên để báo giá cước và quãng đường thực tế một cách lịch sự, chi tiết.
Đặc biệt, khi khách hàng nhờ tư vấn hoặc đề xuất sản phẩm theo nhu cầu (ví dụ: tìm game theo thể loại, hệ máy, mức giá, sở thích hoặc số người chơi...), hãy phân tích kỹ danh sách sản phẩm ở trên để chọn lọc, tư vấn và gợi ý các sản phẩm phù hợp nhất kèm theo đường dẫn markdown của từng game.";

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

                $response = \Illuminate\Support\Facades\Http::timeout(30)
                    ->withOptions([
                        'curl' => [
                            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
                        ],
                        'proxy' => null
                    ])
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                    ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent?key={$apiKey}", [
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
                } else {
                    \Illuminate\Support\Facades\Log::error("Gemini API Request Failed with status " . $response->status() . ": " . $response->body());
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

    private function resolveShopLocation(): array
    {
        $shopAddress = \App\Models\Setting::get('store_address', config('shipping.shop_address'));

        if ($shopAddress === config('shipping.shop_address')) {
            $configuredLat = config('shipping.shop_lat');
            $configuredLng = config('shipping.shop_lng');

            if ($configuredLat !== null && $configuredLng !== null) {
                return [
                    'lat' => (float) $configuredLat,
                    'lng' => (float) $configuredLng,
                    'address' => $shopAddress,
                ];
            }
        }

        $geo = $this->geocodeAddress($shopAddress);

        return [
            'lat' => $geo['lat'] ?? 0.0,
            'lng' => $geo['lng'] ?? 0.0,
            'address' => $shopAddress,
        ];
    }

    private function geocodeAddress(string $address): array
    {
        $result = $this->queryNominatim($address);
        if ($result) {
            return $result;
        }

        $parts = array_map('trim', explode(',', $address));
        while (count($parts) > 1) {
            array_shift($parts);
            $subAddress = implode(', ', $parts);
            $result = $this->queryNominatim($subAddress);
            if ($result) {
                return $result;
            }
        }

        return [];
    }

    private function queryNominatim(string $query): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->withHeaders(['User-Agent' => 'GameStation/1.0'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                ]);

            if (!$response->successful()) {
                return [];
            }

            $first = $response->json()[0] ?? null;
            if (!$first) {
                return [];
            }

            return [
                'lat' => isset($first['lat']) ? (float) $first['lat'] : null,
                'lng' => isset($first['lon']) ? (float) $first['lon'] : null,
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function resolveDistanceKm(array $shop, array $customer): float
    {
        $shopLat = $shop['lat'] ?? null;
        $shopLng = $shop['lng'] ?? null;
        $customerLat = $customer['lat'] ?? null;
        $customerLng = $customer['lng'] ?? null;

        if ($shopLat === null || $shopLng === null || $customerLat === null || $customerLng === null || ($shopLat == 0.0 && $shopLng == 0.0)) {
            return (float) config('shipping.fallback_distance_km', 5);
        }

        $routeKm = $this->fetchDrivingDistanceKm((float) $shopLat, (float) $shopLng, (float) $customerLat, (float) $customerLng);
        if ($routeKm !== null) {
            return $routeKm;
        }

        return $this->haversineDistanceKm((float) $shopLat, (float) $shopLng, (float) $customerLat, (float) $customerLng);
    }

    private function fetchDrivingDistanceKm(float $fromLat, float $fromLng, float $toLat, float $toLng): ?float
    {
        try {
            $url = sprintf(
                'https://router.project-osrm.org/route/v1/driving/%F,%F;%F,%F',
                $fromLng,
                $fromLat,
                $toLng,
                $toLat
            );

            $response = \Illuminate\Support\Facades\Http::timeout(8)->get($url, [
                'overview' => 'false',
                'alternatives' => 'false',
            ]);

            if (!$response->successful()) {
                return null;
            }

            $meters = data_get($response->json(), 'routes.0.distance');
            if (!is_numeric($meters)) {
                return null;
            }

            return max(0.1, round(((float) $meters) / 1000, 2));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function haversineDistanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function resolveRegion(string $province): string
    {
        $province = mb_strtolower($province);
        $province = str_replace(['tỉnh', 'thành phố', 'tp.', 'tp', 'thành phố hồ chí minh', 'thành phố hà nội', 'thành phố hải phòng', 'thành phố đà nẵng', 'thành phố cần thơ'], '', $province);
        $province = trim($province);

        $north = [
            'hà nội', 'hải phòng', 'hà giang', 'cao bằng', 'bắc kạn', 'tuyên quang', 'lào cai', 'điện biên', 
            'lai châu', 'sơn la', 'yên bái', 'hòa bình', 'thái nguyên', 'lạng sơn', 'quảng ninh', 'bắc giang', 
            'phú thọ', 'vĩnh phúc', 'bắc ninh', 'hải dương', 'hưng yên', 'thái bình', 'hà nam', 'nam định', 'ninh bình'
        ];

        $central = [
            'đà nẵng', 'thanh hóa', 'nghệ an', 'hà tĩnh', 'quảng bình', 'quảng trị', 'thừa thiên huế', 'quảng nam', 
            'quảng ngãi', 'bình định', 'phú yên', 'khánh hòa', 'ninh thuận', 'bình thuận', 'kon tum', 'gia lai', 
            'đắk lắk', 'đắk nông', 'lâm đồng', 'thừa thiên-huế', 'huế'
        ];

        $south = [
            'hồ chí minh', 'hcm', 'sài gòn', 'bình dương', 'đồng nai', 'bà rịa - vũng tàu', 'bà rịa vũng tàu', 'vũng tàu', 
            'tây ninh', 'long an', 'tiền giang', 'bến tre', 'trà vinh', 'vĩnh long', 'đồng tháp', 'an giang', 'kiên giang', 
            'cần thơ', 'hậu giang', 'sóc trăng', 'bạc liêu', 'cà mau', 'bình phước', 'đắc nông', 'đăk nông'
        ];

        if (str_contains($province, 'hồ chí minh') || str_contains($province, 'hcm') || str_contains($province, 'sài gòn') || $province === 'hcm' || $province === 'hồ chí minh') {
            return 'local';
        }

        if (in_array($province, $north)) {
            return 'north';
        }

        if (in_array($province, $central)) {
            return 'central';
        }

        if (in_array($province, $south)) {
            return 'south';
        }

        foreach ($north as $n) {
            if (str_contains($province, $n)) return 'north';
        }
        foreach ($central as $c) {
            if (str_contains($province, $c)) return 'central';
        }
        foreach ($south as $s) {
            if (str_contains($province, $s)) return 'south';
        }

        return 'central';
    }
}
