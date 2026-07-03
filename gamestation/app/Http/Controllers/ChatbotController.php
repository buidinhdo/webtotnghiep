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

        // 2. Detect query intent
        $lowerMessage = mb_strtolower($userMessageText);
        $isPriceQuery = false;
        $isLinkQuery = false;
        $isStatusQuery = false;
        $isPlatformQuery = false;
        $isPublisherQuery = false;
        $isDetailsQuery = false;

        $priceKeywords = ['giá', 'bao nhiêu', 'nhiêu', 'tiền', 'đắt', 'rẻ', 'cost', 'price', 'how much'];
        foreach ($priceKeywords as $pk) {
            if (str_contains($lowerMessage, $pk)) {
                $isPriceQuery = true;
                break;
            }
        }

        $linkKeywords = ['link', 'đường dẫn', 'mua', 'order', 'đặt', 'xem'];
        foreach ($linkKeywords as $lk) {
            if (str_contains($lowerMessage, $lk)) {
                $isLinkQuery = true;
                break;
            }
        }

        $statusKeywords = ['tình trạng', 'còn hàng', 'hết hàng', 'còn không', 'sẵn hàng', 'stock', 'tồn', 'ở kho', 'kho'];
        foreach ($statusKeywords as $sk) {
            if (str_contains($lowerMessage, $sk)) {
                $isStatusQuery = true;
                break;
            }
        }

        $platformKeywords = ['hệ máy', 'thiết bị', 'chơi trên', 'chơi bằng', 'platform', 'switch', 'ps5', 'ps4'];
        foreach ($platformKeywords as $plk) {
            if (str_contains($lowerMessage, $plk)) {
                $isPlatformQuery = true;
                break;
            }
        }

        $publisherKeywords = ['nhà phát hành', 'hãng', 'publisher', 'công ty', 'sản xuất'];
        foreach ($publisherKeywords as $pbk) {
            if (str_contains($lowerMessage, $pbk)) {
                $isPublisherQuery = true;
                break;
            }
        }

        $detailsKeywords = ['mô tả', 'thông tin', 'chi tiết', 'cốt truyện', 'nội dung', 'review', 'giới thiệu'];
        foreach ($detailsKeywords as $dk) {
            if (str_contains($lowerMessage, $dk)) {
                $isDetailsQuery = true;
                break;
            }
        }

        $isFollowUpQuery = $isPriceQuery || $isLinkQuery || $isStatusQuery || $isPlatformQuery || $isPublisherQuery || $isDetailsQuery;

        // 3. Perform database search for matching products in the current message
        $cleanString = preg_replace('/[^\p{L}\p{N}\s]/u', '', $lowerMessage);
        $tokens = array_filter(explode(' ', $cleanString));
        
        $stopWords = [
            'có', 'không', 'tìm', 'mua', 'bán', 'game', 'nút', 'thế', 'nào', 'tư', 'vấn', 
            'cho', 'hỏi', 'tôi', 'ở', 'đâu', 'shop', 'cửa', 'hàng', 'admin', 'ad', 'ơi', 
            'nhỉ', 'với', 'cần', 'muốn', 'hiện', 'tại', 'bên', 'mình', 'web', 'sản', 'phẩm', 
            'đĩa', 'máy', 'được', 'lấy', 'cho', 'ra', 'sao', 'này', 'kia', 'đó', 'ạ', 'ko', 'kg',
            'giá', 'bao', 'nhiêu', 'tiền', 'của', 'là', 'về', 'cái', 'nhé', 'nha', 'được', 
            'hộ', 'giúp', 'xin', 'báo', 'xem', 'biết', 'các', 'những', 'một', 'số', 'bản', 'hệ',
            'tình', 'trạng', 'còn', 'hết', 'tin', 'tức', 'mô', 'tả', 'hệ', 'máy'
        ];

        $keywords = array_filter($tokens, function ($token) use ($stopWords) {
            return !in_array($token, $stopWords) && mb_strlen($token) >= 2;
        });

        $products = collect();
        if (!empty($keywords)) {
            $productsQuery = Product::with(['primaryImage', 'images', 'publisher'])
                ->where('is_active', true);
            
            foreach ($keywords as $keyword) {
                $productsQuery->where(function ($builder) use ($keyword) {
                    $builder->where('name', 'like', "%{$keyword}%")
                            ->orWhere('short_description', 'like', "%{$keyword}%");
                });
            }
            
            $products = $productsQuery->take(5)->get();
        }

        // 4. If no products matched, but user is asking a follow-up or message is very short
        // indicating interest in previously discussed product
        if ($products->isEmpty() && ($isFollowUpQuery || mb_strlen($lowerMessage) < 25)) {
            // Find last bot message to extract product IDs from urls
            $lastBotMessage = ChatbotMessage::where('user_id', $user->id)
                ->where('sender', 'bot')
                ->latest()
                ->first();

            if ($lastBotMessage) {
                preg_match_all('/\/products\/(\d+)/', $lastBotMessage->message, $matches);
                if (!empty($matches[1])) {
                    $lastProductIds = array_unique($matches[1]);
                    $products = Product::with(['primaryImage', 'images', 'publisher'])
                        ->whereIn('id', $lastProductIds)
                        ->where('is_active', true)
                        ->get();
                }
            }
        }

        // 5. Format bot response
        if ($products->isNotEmpty()) {
            if ($products->count() === 1) {
                // Highly specific response for single product follow-up or focus
                $product = $products->first();
                $priceFormatted = number_format($product->price, 0, ',', '.') . 'đ';
                $url = route('products.show', $product);
                $statusText = $product->stock > 0 ? "Còn hàng (Số lượng còn: {$product->stock} đĩa)" : "Hết hàng";
                $platform = strtoupper($product->platform ?? 'N/A');
                $pubName = $product->publisher ? $product->publisher->name : 'N/A';

                if ($isStatusQuery) {
                    $botReply = "Sản phẩm **{$product->name}** hiện tại đang: **{$statusText}**.";
                } elseif ($isPlatformQuery) {
                    $botReply = "Sản phẩm **{$product->name}** dành cho hệ máy **{$platform}**.";
                } elseif ($isPublisherQuery) {
                    $botReply = "Sản phẩm **{$product->name}** được phát hành bởi hãng **{$pubName}**.";
                } elseif ($isDetailsQuery) {
                    $desc = $product->short_description ?: ($product->description ?: 'Không có mô tả chi tiết.');
                    $botReply = "Thông tin chi tiết về game **{$product->name}**:\n\n🔹 **Cốt truyện/Mô tả**: " . \Illuminate\Support\Str::limit($desc, 300) . "\n\n🔗 [Xem trang chi tiết sản phẩm]({$url})";
                } elseif ($isPriceQuery && $isLinkQuery) {
                    $botReply = "Sản phẩm **{$product->name}** có giá là **{$priceFormatted}**.\n🔗 Bạn có thể xem chi tiết và đặt mua tại đây: [Xem sản phẩm]({$url})";
                } elseif ($isPriceQuery) {
                    $botReply = "Giá của sản phẩm **{$product->name}** là **{$priceFormatted}** bạn nhé.";
                } elseif ($isLinkQuery) {
                    $botReply = "Đây là đường dẫn chi tiết của sản phẩm **{$product->name}**:\n🔗 [Xem sản phẩm]({$url})";
                } else {
                    // Quick overview
                    $botReply = "Thông tin về sản phẩm **{$product->name}** bạn đang hỏi:\n\n";
                    $botReply .= "🔹 Giá: **{$priceFormatted}**\n";
                    $botReply .= "🔹 Hệ máy: **{$platform}**\n";
                    $botReply .= "🔹 Tình trạng: **{$statusText}**\n";
                    $botReply .= "🔹 Nhà phát hành: **{$pubName}**\n\n";
                    $botReply .= "🔗 [Xem chi tiết sản phẩm]({$url})";
                }
            } else {
                // General list response for multiple products
                $botReply = "Chào bạn! Tôi tìm thấy một số sản phẩm phù hợp với yêu cầu của bạn:\n\n";
                foreach ($products as $product) {
                    $priceFormatted = number_format($product->price, 0, ',', '.') . 'đ';
                    $url = route('products.show', $product);
                    $botReply .= "🔹 **{$product->name}**\n";
                    $botReply .= "   Platform: " . strtoupper($product->platform ?? 'N/A') . "\n";
                    $botReply .= "   Giá: {$priceFormatted}\n";
                    $botReply .= "   🔗 [Xem chi tiết sản phẩm](" . $url . ")\n\n";
                }
                $botReply .= "Bạn có cần tư vấn thêm gì về các sản phẩm này không?";
            }
        } else {
            $botReply = "Chào bạn! Hiện tại tôi chưa tìm thấy sản phẩm nào khớp hoàn toàn với mô tả của bạn. Câu hỏi của bạn đã được ghi nhận, và quản trị viên của chúng tôi sẽ sớm phản hồi hỗ trợ bạn trực tiếp tại đây nhé!";
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
}
