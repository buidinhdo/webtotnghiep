@extends('admin.layout')

@section('title', 'Sửa bài viết')
@section('page_title', 'Sửa bài viết')
@section('breadcrumb', 'Sửa bài viết')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Thông tin bài viết</h3>
    </div>
    <form action="{{ route('admin.articles.update', $article) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Tiêu đề <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" required value="{{ old('title', $article->title) }}">
                @error('title')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>Tóm tắt</label>
                <textarea name="excerpt" class="form-control @error('excerpt') is-invalid @enderror" rows="2" placeholder="Tóm tắt ngắn của bài viết">{{ old('excerpt', $article->excerpt) }}</textarea>
                @error('excerpt')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>Nội dung <span class="text-danger">*</span></label>
                <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="8" required>{{ old('content', $article->content) }}</textarea>
                @error('content')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <!-- Ảnh hiện tại -->
            @if($article->image_path)
                <div class="mb-3">
                    <label>Ảnh hiện tại:</label><br>
                    <img src="{{ asset($article->image_path) }}" alt="Article" style="max-width: 200px; max-height: 200px; border-radius: 5px; border: 1px solid #ddd;">
                </div>
            @endif

            <div class="form-group">
                <label>Thay đổi ảnh (để trống nếu không thay đổi)</label>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input @error('image') is-invalid @enderror" id="articleImage" name="image" accept="image/*,.webp">
                        <label class="custom-file-label" for="articleImage">Chọn ảnh...</label>
                    </div>
                </div>
                <small class="form-text text-muted">JPG, PNG, GIF, WEBP (Tối đa 5MB)</small>
                <div id="imagePreview" class="mt-3"></div>
                @error('image')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-check">
                <input type="checkbox" name="is_published" class="form-check-input" id="isPublished" value="1" {{ $article->is_published ? 'checked' : '' }}>
                <label class="form-check-label" for="isPublished">
                    Xuất bản
                </label>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">Hủy</a>
        </div>
    </form>
</div>

<script>
document.getElementById('articleImage').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const file = e.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            preview.innerHTML = '<img src="' + event.target.result + '" style="max-width: 200px; max-height: 200px; border-radius: 5px; border: 1px solid #ddd;">';
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
});

// Update label text
document.querySelector('#articleImage').addEventListener('change', function() {
    const label = document.querySelector('label[for="articleImage"]');
    label.textContent = this.files[0]?.name || 'Chọn ảnh...';
});
</script>
@endsection
