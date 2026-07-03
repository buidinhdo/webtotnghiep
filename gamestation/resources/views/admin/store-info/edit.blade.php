@extends('admin.layout')

@section('title', 'Thông tin cửa hàng')
@section('page_title', 'Thông tin cửa hàng')
@section('breadcrumb', 'Thông tin cửa hàng')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Cập nhật thông tin cửa hàng</h3>
    </div>
    
    <form method="POST" action="{{ route('admin.store-info.update') }}">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="store_name">Tên cửa hàng <span class="text-danger">*</span></label>
                        <input type="text" name="store_name" id="store_name" value="{{ old('store_name', $settings['store_name']) }}" class="form-control @error('store_name') is-invalid @enderror" placeholder="Nhập tên cửa hàng" required>
                        @error('store_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="store_hotline">Số điện thoại Hotline <span class="text-danger">*</span></label>
                        <input type="text" name="store_hotline" id="store_hotline" value="{{ old('store_hotline', $settings['store_hotline']) }}" class="form-control @error('store_hotline') is-invalid @enderror" placeholder="Nhập hotline" required>
                        @error('store_hotline')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="store_email">Địa chỉ Email <span class="text-danger">*</span></label>
                        <input type="email" name="store_email" id="store_email" value="{{ old('store_email', $settings['store_email']) }}" class="form-control @error('store_email') is-invalid @enderror" placeholder="Nhập email hỗ trợ" required>
                        @error('store_email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="store_address">Địa chỉ tính phí ship (Địa chỉ Shop) <span class="text-danger">*</span></label>
                        <input type="text" name="store_address" id="store_address" value="{{ old('store_address', $settings['store_address']) }}" class="form-control @error('store_address') is-invalid @enderror" placeholder="Ví dụ: 123 Nguyễn Huệ, TP. HCM" required>
                        <small class="form-text text-muted">Dùng để định vị khoảng cách và tính tiền phí vận chuyển khi khách mua hàng.</small>
                        @error('store_address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="store_address_hcm">Địa chỉ chi nhánh Hồ Chí Minh <span class="text-danger">*</span></label>
                        <input type="text" name="store_address_hcm" id="store_address_hcm" value="{{ old('store_address_hcm', $settings['store_address_hcm']) }}" class="form-control @error('store_address_hcm') is-invalid @enderror" placeholder="Địa chỉ chi nhánh miền Nam" required>
                        @error('store_address_hcm')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="store_address_hn">Địa chỉ chi nhánh Hà Nội <span class="text-danger">*</span></label>
                        <input type="text" name="store_address_hn" id="store_address_hn" value="{{ old('store_address_hn', $settings['store_address_hn']) }}" class="form-control @error('store_address_hn') is-invalid @enderror" placeholder="Địa chỉ chi nhánh miền Bắc" required>
                        @error('store_address_hn')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="store_description">Mô tả chân trang (Footer Description) <span class="text-danger">*</span></label>
                <textarea name="store_description" id="store_description" rows="3" class="form-control @error('store_description') is-invalid @enderror" placeholder="Nhập mô tả ngắn chân trang..." required>{{ old('store_description', $settings['store_description']) }}</textarea>
                @error('store_description')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Lưu thay đổi</button>
        </div>
    </form>
</div>
@endsection
