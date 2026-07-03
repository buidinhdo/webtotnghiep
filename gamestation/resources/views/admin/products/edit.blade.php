@extends('admin.layout')

@section('title', 'Sửa sản phẩm')
@section('page_title', 'Sửa sản phẩm')
@section('breadcrumb', 'Sửa sản phẩm')

@section('content')
<form action="{{ route('admin.products.update', $product) }}{{ request()->query() ? '?' . http_build_query(request()->query()) : '' }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="hidden" name="page" value="{{ request()->get('page') }}">
    
    <!-- Basic Information Section -->
    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title text-white"><i class="fas fa-info-circle mr-2"></i>Thông tin cơ bản</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Tên sản phẩm <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required value="{{ $product->name }}" placeholder="Nhập tên sản phẩm">
                @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>Mô tả ngắn</label>
                <textarea name="short_description" class="form-control @error('short_description') is-invalid @enderror" rows="3" placeholder="Mô tả ngắn gọn sản phẩm">{{ $product->short_description }}</textarea>
                @error('short_description')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>Thông số chi tiết</label>
                <textarea name="detailed_description" class="form-control @error('detailed_description') is-invalid @enderror" rows="5" placeholder="Mỗi dòng một thông số, ví dụ: Thể loại: Sports">{{ $product->detailed_description }}</textarea>
                @error('detailed_description')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <!-- Categorization Section -->
    <div class="card mt-3">
        <div class="card-header bg-info">
            <h3 class="card-title text-white"><i class="fas fa-tags mr-2"></i>Phân loại</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Danh mục <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nền tảng</label>
                        <select name="platform" class="form-control @error('platform') is-invalid @enderror">
                            <option value="">-- Chọn nền tảng --</option>
                            <option value="PS5" {{ $product->platform == 'PS5' ? 'selected' : '' }}>PS5</option>
                            <option value="PS4" {{ $product->platform == 'PS4' ? 'selected' : '' }}>PS4</option>
                            <option value="Xbox" {{ $product->platform == 'Xbox' ? 'selected' : '' }}>Xbox</option>
                            <option value="Switch" {{ $product->platform == 'Switch' ? 'selected' : '' }}>Nintendo Switch</option>
                            <option value="PC" {{ $product->platform == 'PC' ? 'selected' : '' }}>PC</option>
                        </select>
                        @error('platform')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Thể loại (Genre)</label>
                        <select name="genre" class="form-control @error('genre') is-invalid @enderror">
                            <option value="">-- Chọn thể loại --</option>
                            @foreach($genres as $genre)
                                <option value="{{ $genre->name }}" {{ old('genre', $product->genre) == $genre->name ? 'selected' : '' }}>{{ $genre->name }}</option>
                            @endforeach
                        </select>
                        @error('genre')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>ESRB</label>
                        <select name="esrb" class="form-control @error('esrb') is-invalid @enderror">
                            <option value="">-- Không chọn --</option>
                            <option value="EC" {{ $product->esrb == 'EC' ? 'selected' : '' }}>EC - Early Childhood</option>
                            <option value="E" {{ $product->esrb == 'E' ? 'selected' : '' }}>E - Everyone</option>
                            <option value="E10+" {{ $product->esrb == 'E10+' ? 'selected' : '' }}>E10+ - Everyone 10+</option>
                            <option value="T" {{ $product->esrb == 'T' ? 'selected' : '' }}>T - Teen</option>
                            <option value="M" {{ $product->esrb == 'M' ? 'selected' : '' }}>M - Mature</option>
                            <option value="AO" {{ $product->esrb == 'AO' ? 'selected' : '' }}>AO - Adults Only 18+</option>
                            <option value="RP" {{ $product->esrb == 'RP' ? 'selected' : '' }}>RP - Rating Pending</option>
                        </select>
                        @error('esrb')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing & Stock Section -->
    <div class="card mt-3">
        <div class="card-header bg-success">
            <h3 class="card-title text-white"><i class="fas fa-dollar-sign mr-2"></i>Giá & Tồn kho</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Giá gốc <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" required step="0.01" value="{{ $product->price }}" placeholder="0.00">
                            <div class="input-group-append">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>
                        @error('price')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tồn kho <span class="text-danger">*</span></label>
                        <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" required value="{{ $product->stock }}" placeholder="0">
                        @error('stock')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Game Details Section -->
    <div class="card mt-3">
        <div class="card-header bg-warning">
            <h3 class="card-title"><i class="fas fa-gamepad mr-2"></i>Chi tiết trò chơi</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nhà phát hành</label>
                        <select name="publisher_id" class="form-control @error('publisher_id') is-invalid @enderror">
                            <option value="">-- Chọn nhà phát hành --</option>
                            @foreach($publishers as $pub)
                                <option value="{{ $pub->id }}" {{ $product->publisher_id == $pub->id ? 'selected' : '' }}>{{ $pub->name }}</option>
                            @endforeach
                        </select>
                        @error('publisher_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Media Section -->
    <div class="card mt-3">
        <div class="card-header bg-danger">
            <h3 class="card-title text-white"><i class="fas fa-images mr-2"></i>Hình ảnh sản phẩm</h3>
        </div>
        <div class="card-body">
            <!-- Current Primary Image -->
            @if($product->primaryImage)
                <div class="mb-4 p-3 bg-light rounded">
                    <label class="font-weight-bold mb-2 d-block">Ảnh chính hiện tại:</label>
                    <img src="{{ asset($product->primaryImage->image_path) }}" alt="Primary" style="max-width: 150px; max-height: 150px; border-radius: 8px; border: 2px solid #ddd;">
                </div>
            @endif

            <!-- Change Primary Image -->
            <div class="form-group">
                <label><i class="fas fa-star text-warning"></i> Thay đổi ảnh chính (để trống nếu không thay đổi)</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input @error('primary_image') is-invalid @enderror" id="primaryImage" name="primary_image" accept="image/*">
                    <label class="custom-file-label" for="primaryImage">Chọn ảnh chính...</label>
                </div>
                @error('primary_image')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
                <small class="form-text text-muted d-block mt-2">JPG, PNG, GIF (Tối đa 5MB)</small>
                <div id="primaryImagePreview" class="mt-3"></div>
            </div>

            <hr>

            <!-- Current Additional Images (cannot be deleted here) -->
            @if($product->images()->where('is_primary', false)->count() > 0)
                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                        <label class="font-weight-bold mb-0 d-block">Ảnh phụ hiện tại:</label>
                        <button
                            type="button"
                            class="btn btn-danger btn-sm mt-2 mt-sm-0"
                            data-delete-all-images-url="{{ route('admin.products.images.destroy-all', $product) }}"
                            data-delete-all-images-name="tất cả ảnh phụ"
                        >
                            <i class="fas fa-trash-alt mr-1"></i>Xóa tất cả ảnh phụ
                        </button>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($product->images()->where('is_primary', false)->get() as $image)
                            <div class="image-preview">
                                <img src="{{ asset($image->image_path) }}" alt="Product" style="border: 2px solid #e9ecef;">
                                <button
                                    type="button"
                                    class="btn btn-danger btn-xs image-delete-btn"
                                    title="Xóa ảnh phụ"
                                    data-delete-image-url="{{ route('admin.products.images.destroy', [$product, $image]) }}"
                                    data-delete-image-name="ảnh phụ này"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
                <hr>
            @endif

            <!-- Add Additional Images -->
            <div class="form-group">
                <label><i class="fas fa-image"></i> Thêm ảnh phụ (Có thể chọn nhiều ảnh)</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input @error('images.*') is-invalid @enderror" id="multipleImages" name="images[]" multiple accept="image/*">
                    <label class="custom-file-label" for="multipleImages">Chọn ảnh phụ...</label>
                </div>
                @error('images.*')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
                <small class="form-text text-muted d-block mt-2">JPG, PNG, GIF (Tối đa 5MB mỗi ảnh) - Những ảnh này sẽ được hiển thị trong trang chi tiết sản phẩm</small>
                <div id="multipleImagesPreview" class="mt-3 d-flex flex-wrap gap-2"></div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="card mt-3">
        <div class="card-footer bg-light">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>Cập nhật sản phẩm
            </button>
            <a href="{{ route('admin.products.index', request()->query()) }}" class="btn btn-secondary">
                <i class="fas fa-times mr-2"></i>Hủy
            </a>
        </div>
    </div>
</form>

<style>
    .card-header.bg-primary,
    .card-header.bg-info,
    .card-header.bg-success,
    .card-header.bg-warning,
    .card-header.bg-danger {
        border-bottom: 3px solid rgba(0,0,0,0.1);
    }

    .card-header .card-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .gap-2 {
        gap: 0.5rem;
    }

    .image-preview {
        position: relative;
        display: inline-block;
        margin-right: 10px;
        margin-bottom: 10px;
    }

    .image-delete-btn {
        position: absolute;
        top: 6px;
        right: 6px;
        padding: 2px 6px;
        line-height: 1;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    .image-preview img {
        max-width: 120px;
        max-height: 120px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .image-preview:hover img {
        border-color: #0d6efd;
    }

    .input-group-text {
        font-weight: 600;
    }
</style>

<script>
document.getElementById('primaryImage').addEventListener('change', function(e) {
    const preview = document.getElementById('primaryImagePreview');
    preview.innerHTML = '';
    
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const div = document.createElement('div');
            div.className = 'image-preview';
            const img = document.createElement('img');
            img.src = event.target.result;
            div.appendChild(img);
            preview.appendChild(div);
        };
        reader.readAsDataURL(this.files[0]);
    }
});

document.getElementById('multipleImages').addEventListener('change', function(e) {
    const preview = document.getElementById('multipleImagesPreview');
    preview.innerHTML = '';
    
    if (this.files) {
        Array.from(this.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const div = document.createElement('div');
                div.className = 'image-preview';
                const img = document.createElement('img');
                img.src = event.target.result;
                div.appendChild(img);
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
});

// Update file labels
document.querySelector('#primaryImage').addEventListener('change', function() {
    const label = document.querySelector('label[for="primaryImage"]');
    label.textContent = this.files.length > 0 ? this.files[0].name : 'Chọn ảnh chính...';
});

document.querySelector('#multipleImages').addEventListener('change', function() {
    const label = document.querySelector('label[for="multipleImages"]');
    label.textContent = this.files.length > 0 ? `${this.files.length} ảnh đã chọn` : 'Chọn ảnh phụ...';
});

const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

async function deleteImages(url, name) {
    if (!confirm(`Bạn chắc chắn muốn xóa ${name}?`)) {
        return;
    }

    try {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Xóa ảnh thất bại');
        }

        window.location.reload();
    } catch (error) {
        alert(error.message || 'Xóa ảnh thất bại');
    }
}

document.querySelectorAll('[data-delete-all-images-url]').forEach((button) => {
    button.addEventListener('click', async () => {
        await deleteImages(
            button.dataset.deleteAllImagesUrl,
            button.dataset.deleteAllImagesName || 'tất cả ảnh phụ'
        );
    });
});

document.querySelectorAll('[data-delete-image-url]').forEach((button) => {
    button.addEventListener('click', async () => {
        await deleteImages(
            button.dataset.deleteImageUrl,
            button.dataset.deleteImageName || 'ảnh này'
        );
    });
});
</script>
@endsection
