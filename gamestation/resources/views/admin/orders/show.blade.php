@extends('admin.layout')

@section('title', 'Chi tiết đơn hàng')
@section('page_title', 'Chi tiết đơn hàng ' . $order->id)
@section('breadcrumb', 'Chi tiết đơn hàng')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Thông tin sản phẩm</h3>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Tổng cộng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td style="width: 90px;">
                                @php
                                    $productImage = $item->product?->primaryImage?->image_path
                                        ?? $item->product?->images?->first()?->image_path;
                                @endphp
                                <img
                                    src="{{ $productImage ? asset($productImage) : 'https://placehold.co/80x80' }}"
                                    alt="{{ $item->product_name }}"
                                    style="width: 72px; height: 72px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6;"
                                >
                            </td>
                            <td>{{ $item->product_name ?? $item->product?->name ?? 'N/A' }}</td>
                            <td>{{ number_format($item->price, 0, ',', '.') }}đ</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Cập nhật trạng thái đơn hàng</h3>
            </div>
            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Đang giao</option>
                            <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Thông tin khách hàng</h3>
            </div>
            <div class="card-body">
                <strong>Tên:</strong> {{ $order->user->name ?? $order->shipping_name ?? 'N/A' }}<br>
                <strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}<br>
                <strong>Điện thoại:</strong> {{ $order->shipping_phone ?? $order->user->phone ?? 'N/A' }}<br>
                <strong>Địa chỉ:</strong> {{ $order->shipping_address ?? $order->user->address ?? 'N/A' }}
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tóm tắt đơn hàng</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">Tạm tính:</div>
                    <div class="col-6 text-right">{{ number_format($order->subtotal, 0, ',', '.') }}đ</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">Giảm giá:</div>
                    <div class="col-6 text-right">-{{ number_format($order->discount, 0, ',', '.') }}đ</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">Phí giao hàng:</div>
                    <div class="col-6 text-right">{{ number_format($order->shipping_fee ?? 0, 0, ',', '.') }}đ</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">Khoảng cách:</div>
                    <div class="col-6 text-right">{{ number_format((float)($order->shipping_distance_km ?? 0), 2, ',', '.') }} km</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">Tổng cộng:</div>
                    <div class="col-6 text-right"><strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">Trạng thái:</div>
                    <div class="col-6 text-right">
                        <span class="badge badge-{{ $order->status == 'completed' ? 'success' : ($order->status == 'cancelled' ? 'danger' : 'info') }}">
                            {{ $order->status_label }}
                        </span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">Ngày tạo:</div>
                    <div class="col-6 text-right">{{ $order->created_at?->format('d/m/Y H:i') }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">Thanh toán:</div>
                    <div class="col-6 text-right">
                        {{ $order->payment_method_label }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">Giao hàng:</div>
                    <div class="col-6 text-right">
                        {{ $order->shipping_method === 'express' ? 'Nhanh (24h-48h)' : 'Tiêu chuẩn (2-4 ngày)' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
