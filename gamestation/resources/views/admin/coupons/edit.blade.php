@extends('admin.layout')

@section('title', 'Sửa mã giảm giá')
@section('page_title', 'Sửa mã giảm giá')
@section('breadcrumb', 'Sửa mã giảm giá')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Sửa mã giảm giá</h3>
        <div class="card-tools">
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Mã giảm giá <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $coupon->code) }}" required>
            </div>
            <div class="form-group">
                <label>Loại giảm giá <span class="text-danger">*</span></label>
                <select name="type" class="form-control" required>
                    <option value="percentage" {{ old('type', $coupon->type) == 'percentage' ? 'selected' : '' }}>Phần trăm (%)</option>
                    <option value="fixed" {{ old('type', $coupon->type) == 'fixed' ? 'selected' : '' }}>Số tiền cố định (đ)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Giá trị <span class="text-danger">*</span></label>
                <input type="number" name="value" class="form-control" value="{{ old('value', $coupon->value) }}" min="0" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Đơn hàng tối thiểu (đ)</label>
                <input type="number" name="min_order" class="form-control" value="{{ old('min_order', $coupon->min_order) }}" min="0">
            </div>
            <div class="form-group">
                <label>Giới hạn số lần dùng</label>
                <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit', $coupon->usage_limit) }}" min="1">
                <small class="text-muted">Để trống = không giới hạn</small>
            </div>
            <div class="form-group">
                <label>Ngày bắt đầu</label>
                <input type="date" name="starts_at" class="form-control" value="{{ old('starts_at', $coupon->starts_at ? \Carbon\Carbon::parse($coupon->starts_at)->format('Y-m-d') : '') }}">
            </div>
            <div class="form-group">
                <label>Ngày hết hạn</label>
                <input type="date" name="ends_at" class="form-control" value="{{ old('ends_at', $coupon->ends_at ? \Carbon\Carbon::parse($coupon->ends_at)->format('Y-m-d') : '') }}">
            </div>
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_active">Kích hoạt</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Cập nhật
            </button>
        </form>
    </div>
</div>
@endsection