@extends('admin.layout')

@section('title', 'Quản lý đơn hàng')
@section('page_title', 'Danh sách đơn hàng')
@section('breadcrumb', 'Đơn hàng')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quản lý đơn hàng</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3 mb-4">
            <div class="col-md-3 mb-2">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Mã ĐH, tên, SĐT, email...">
            </div>
            <div class="col-md-2 mb-2">
                <select name="status" class="form-control form-control-sm">
                    <option value="">-- Trạng thái ĐH --</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Đang giao</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select name="payment_status" class="form-control form-control-sm">
                    <option value="">-- Trạng thái TT --</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                    <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Đã hoàn tiền</option>
                    <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Thất bại</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Chờ thanh toán</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select name="payment_method" class="form-control form-control-sm">
                    <option value="">-- PTTT --</option>
                    <option value="cod" {{ request('payment_method') === 'cod' ? 'selected' : '' }}>COD</option>
                    <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Tiền mặt</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <div class="input-group input-group-sm">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control" title="Từ ngày">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control" title="Đến ngày">
                </div>
            </div>
            <div class="col-12 mt-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Lọc</button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm ml-2"><i class="fas fa-sync-alt"></i> Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">STT</th>
                    <th>Khách hàng</th>
                    <th>Email</th>
                    <th>Số tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th style="width: 150px">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td><strong>{{ $order->user->name ?? 'N/A' }}</strong></td>
                    <td>{{ $order->user->email ?? 'N/A' }}</td>
                    <td>{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                    <td>
                        <span class="badge badge-{{ $order->status == 'completed' ? 'success' : ($order->status == 'cancelled' ? 'danger' : 'info') }}">
                            {{ $order->status_label }}
                        </span>
                    </td>
                    <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-info btn-xs">
                            <i class="fas fa-eye"></i> Chi tiết
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">Không có đơn hàng nào</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
    <div class="card-footer">
        @php
            $lastPage = $orders->lastPage();
            $currentPage = $orders->currentPage();
            $pageNumbers = $orders->getUrlRange(1, $lastPage);
        @endphp
        <nav aria-label="Phân trang đơn hàng">
            <ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap admin-pagination-numeric">
                @foreach ($pageNumbers as $page => $url)
                    <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>

    <style>
        .admin-pagination-numeric .page-link { min-width: 44px; text-align: center; }
        .admin-pagination-numeric .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; color: #fff; }
    </style>
</div>
@endsection
