@extends('admin.layout')

@section('title', 'Sửa banner')
@section('page_title', 'Sửa banner')
@section('breadcrumb', 'Sửa banner')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Sửa banner</h3>
    </div>
    <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Banner hiện tại</label>
                <div class="mb-3">
                    <img src="{{ asset($banner->image_path) }}" alt="{{ basename($banner->image_path) }}" style="max-width: 100%; max-height: 400px; border-radius: 5px; border: 1px solid #ddd; object-fit: cover;">
                </div>
                <small class="form-text text-muted">{{ basename($banner->image_path) }}</small>
            </div>
            
            <div class="form-group">
                <label>Chọn ảnh mới <span class="text-danger">*</span></label>
                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*,.webp" required>
                @error('image')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <small class="form-text text-muted">Định dạng: JPEG, PNG, GIF, WEBP. Kích thước tối đa: 5MB</small>
            </div>
            <div id="image-preview" class="mt-2">
                <img id="preview" src="" alt="Preview" style="max-width: 100%; max-height: 300px; display: none; border-radius: 5px; border: 1px solid #ddd;">
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Cập nhật banner</button>
            <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">Hủy</a>
        </div>
    </form>
</div>

@section('extra_js')
<script>
    document.querySelector('input[name="image"]').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('preview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection

@endsection
