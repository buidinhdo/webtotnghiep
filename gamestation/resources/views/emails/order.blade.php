<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Xác nhận đơn hàng</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #ffffff; }
        .header { background: #1a202c; color: #ffffff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .details { margin: 20px 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #cbd5e1; padding: 12px; text-align: left; }
        .table th { background-color: #f8fafc; font-weight: bold; }
        .total-box { text-align: right; margin-top: 20px; font-size: 15px; line-height: 2; border-top: 2px solid #e2e8f0; padding-top: 15px; }
        .grand-total { font-weight: bold; font-size: 18px; color: #dc2626; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0;">Cảm ơn bạn đã mua hàng!</h2>
        </div>
        
        <div class="details">
            <p>Xin chào <strong>{{ $order->shipping_name }}</strong>,</p>
            <p>Chúng tôi xin gửi thông tin xác nhận hóa đơn cho đơn hàng của bạn:</p>
            
            <p><strong>Mã đơn hàng:</strong> #{{ $order->id }}</p>
            <p><strong>Thời gian đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Số điện thoại nhận hàng:</strong> {{ $order->shipping_phone }}</p>
            <p><strong>Địa chỉ giao hàng:</strong> {{ $order->shipping_address }}</p>
            <p><strong>Phương thức thanh toán:</strong> 
                {{ $order->payment_method === 'credit_card' ? 'Thẻ tín dụng (VNPay)' : 'Thanh toán khi nhận hàng (COD)' }}
            </p>
            <p><strong>Trạng thái thanh toán:</strong> 
                <span style="color: {{ $order->payment_status === 'paid' ? '#16a34a' : '#ea580c' }}; font-weight: bold;">
                    {{ $order->payment_status === 'paid' ? 'Đã thanh toán thành công' : 'Chưa thanh toán (COD)' }}
                </span>
            </p>
            <p><strong>Trạng thái đơn hàng:</strong> 
                <span style="color: #1e40af; font-weight: bold;">
                    @switch($order->status)
                        @case('pending') Chờ xử lý @break
                        @case('processing') Đang xử lý @break
                        @case('shipped') Đang giao hàng @break
                        @case('completed') Đã hoàn thành @break
                        @case('cancelled') Đã hủy @break
                        @default {{ $order->status }}
                    @endswitch
                </span>
            </p>
        </div>

        <h3>Chi tiết sản phẩm đã mua:</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price, 0, ',', '.') }}đ</td>
                    <td>{{ number_format($item->total, 0, ',', '.') }}đ</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-box">
            <div>Tạm tính: <strong>{{ number_format($order->subtotal, 0, ',', '.') }}đ</strong></div>
            @if($order->discount > 0)
                <div>Giảm giá: <strong style="color: #16a34a;">-{{ number_format($order->discount, 0, ',', '.') }}đ</strong></div>
            @endif
            <div>Phí vận chuyển: <strong>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</strong></div>
            <div class="grand-total">Tổng cộng: {{ number_format($order->total, 0, ',', '.') }}đ</div>
        </div>

        <div class="footer">
            <p>Mọi thắc mắc vui lòng liên hệ bộ phận CSKH của GameStation.</p>
            <p>Chúc bạn chơi game vui vẻ!</p>
        </div>
    </div>
</body>
</html>
