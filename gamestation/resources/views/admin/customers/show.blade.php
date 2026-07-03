@extends('admin.layout')

@section('title', 'Chi tiết khách hàng')
@section('page_title', 'Chi tiết khách hàng')
@section('breadcrumb', 'Chi tiết khách hàng')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Thông tin khách hàng</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label><strong>Tên:</strong></label>
                    <p>{{ $customer->name }}</p>
                </div>
                <div class="form-group">
                    <label><strong>Email:</strong></label>
                    <p>{{ $customer->email }}</p>
                </div>
                <div class="form-group">
                    <label><strong>Điện thoại:</strong></label>
                    <p>{{ $customer->phone ?? 'N/A' }}</p>
                </div>
                <div class="form-group">
                    <label><strong>Địa chỉ:</strong></label>
                    <p>{{ $customer->address ?? 'N/A' }}</p>
                </div>
                <div class="form-group">
                    <label><strong>Ngày tạo:</strong></label>
                    <p>{{ $customer->created_at?->format('d/m/Y H:i') }}</p>
                </div>
                <div class="form-group">
                    <label><strong>Trạng thái:</strong></label>
                    <p>
                        <span class="badge badge-{{ $customer->is_active ? 'success' : 'danger' }}">
                            {{ $customer->is_active ? 'Hoạt động' : 'Đã khóa' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Hành động</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.customers.toggleStatus', $customer) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-block btn-{{ $customer->is_active ? 'danger' : 'success' }}">
                        <i class="fas fa-{{ $customer->is_active ? 'lock' : 'unlock' }}"></i>
                        {{ $customer->is_active ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
