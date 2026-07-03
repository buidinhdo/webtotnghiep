@extends('admin.layout')

@section('title', 'Thêm banner')
@section('page_title', 'Thêm banner mới')
@section('breadcrumb', 'Thêm banner')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Thêm banner mới</h3>
    </div>
    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Chọn ảnh <span class="text-danger">*</span></label>
                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*,.webp" required>
                @error('image')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <small class="form-text text-muted">Định dạng: JPEG, PNG, GIF, WEBP. Kích thước tối đa: 5MB</small>
            </div>
            <div id="image-preview" class="mt-2">
                <img id="preview" src="" alt="Preview" style="max-width: 100%; max-height: 300px; display: none;">
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Thêm banner</button>
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
