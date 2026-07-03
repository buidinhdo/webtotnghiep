@extends('admin.layout')

@section('title', 'Quản lý mã giảm giá')
@section('page_title', 'Quản lý mã giảm giá')
@section('breadcrumb', 'Quản lý mã giảm giá')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Danh sách mã giảm giá</h3>
        <div class="card-tools">
            <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm mã giảm giá
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.coupons.index') }}" class="row g-3 mb-4">
            <div class="col-md-4 mb-2">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Tìm kiếm mã giảm giá...">
            </div>
            <div class="col-md-3 mb-2">
                <select name="type" class="form-control form-control-sm">
                    <option value="">-- Loại giảm giá --</option>
                    <option value="percentage" {{ request('type') === 'percentage' ? 'selected' : '' }}>Phần trăm</option>
                    <option value="fixed" {{ request('type') === 'fixed' ? 'selected' : '' }}>Cố định</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <select name="status" class="form-control form-control-sm">
                    <option value="">-- Trạng thái --</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Đã tắt</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Chưa bắt đầu</option>
                    <option value="maxed" {{ request('status') === 'maxed' ? 'selected' : '' }}>Hết lượt sử dụng</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-filter"></i> Lọc</button>
            </div>
            @if(request()->anyFilled(['search', 'type', 'status']))
                <div class="col-12 text-right mt-1">
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-sync-alt"></i> Reset bộ lọc</a>
                </div>
            @endif
        </form>

        @if($coupons->count())
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mã</th>
                        <th>Loại</th>
                        <th>Giá trị</th>
                        <th>Đơn tối thiểu</th>
                        <th>Đã dùng / Giới hạn</th>
                        <th>Ngày bắt đầu</th>
                        <th>Ngày hết hạn</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coupons as $coupon)
                    <tr>
                        <td>{{ $coupon->id }}</td>
                        <td><strong>{{ $coupon->code }}</strong></td>
                        <td>
                            @if($coupon->type === 'percentage')
                                <span class="badge badge-info">Phần trăm</span>
                            @else
                                <span class="badge badge-secondary">Cố định</span>
                            @endif
                        </td>
                        <td>
                            @if($coupon->type === 'percentage')
                                {{ $coupon->value }}%
                            @else
                                {{ number_format($coupon->value, 0, ',', '.') }}đ
                            @endif
                        </td>
                        <td>{{ $coupon->min_order ? number_format($coupon->min_order, 0, ',', '.') . 'đ' : '—' }}</td>
                        <td>{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}</td>
                        <td>{{ $coupon->starts_at ? $coupon->starts_at->format('d/m/Y') : '—' }}</td>
                        <td>{{ $coupon->ends_at ? $coupon->ends_at->format('d/m/Y') : '—' }}</td>
                        <td>
                            @if($coupon->is_active)
                                <span class="badge badge-success">Hoạt động</span>
                            @else
                                <span class="badge badge-danger">Tắt</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @php
            $lastPage = $coupons->lastPage();
            $currentPage = $coupons->currentPage();
            $pageNumbers = $coupons->getUrlRange(1, $lastPage);
        @endphp
        @if($lastPage > 1)
        <nav aria-label="Phân trang mã giảm giá">
            <ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap admin-pagination-numeric">
                @foreach ($pageNumbers as $page => $url)
                    <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
        @endif

        <style>
            .admin-pagination-numeric .page-link { min-width: 44px; text-align: center; }
            .admin-pagination-numeric .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; color: #fff; }
        </style>
        @else
        <div class="alert alert-info">
            Chưa có mã giảm giá nào. <a href="{{ route('admin.coupons.create') }}">Thêm mới</a>
        </div>
        @endif
    </div>
</div>
@endsection