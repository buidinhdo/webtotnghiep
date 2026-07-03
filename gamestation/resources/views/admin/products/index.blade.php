@extends('admin.layout')

@section('title', 'Quản lý sản phẩm')
@section('page_title', 'Danh sách sản phẩm')
@section('breadcrumb', 'Sản phẩm')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quản lý sản phẩm</h3>
        <div class="card-tools">
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm sản phẩm
            </a>
        </div>
    </div>
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('admin.products.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label">Danh mục</label>
                <select name="category_id" class="form-control">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Nhà phát hành</label>
                <select name="publisher_id" class="form-control">
                    <option value="">Tất cả nhà phát hành</option>
                    @foreach($publishers as $publisher)
                        <option value="{{ $publisher->id }}" @selected(request('publisher_id') == $publisher->id)>{{ $publisher->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Thể loại</label>
                <select name="genre" class="form-control">
                    <option value="">Tất cả thể loại</option>
                    @foreach($genres as $item)
                        <option value="{{ $item->name }}" @selected(request('genre') === $item->name)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">ESRB</label>
                <select name="esrb" class="form-control">
                    <option value="">Tất cả ESRB</option>
                    @foreach($esrbRatings as $rating)
                        <option value="{{ $rating->code }}" @selected(request('esrb') === $rating->code)>{{ $rating->code }} - {{ $rating->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mt-3 d-flex flex-wrap filter-actions">
                <button type="submit" class="btn btn-primary btn-sm mr-2 mb-2">
                    <i class="fas fa-filter mr-1"></i>Lọc
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fas fa-undo mr-1"></i>Đặt lại
                </a>
            </div>
        </form>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">STT</th>
                    <th style="width: 150px">Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Mô tả ngắn</th>
                    <th>Danh mục</th>
                    <th>Nhà phát hành</th>
                    <th>Thể loại</th>
                    <th>ESRB</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th style="width: 200px">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>
                    <td>
                        @php $pimg = $product->primaryImage ?? $product->images->first(); @endphp
                        @if($pimg && $pimg->image_path)
                            <div style="position: relative;">
                                <img src="{{ asset($pimg->image_path) }}" alt="{{ $product->name }}" style="max-width: 100px; max-height: 100px; border-radius: 5px; border: 1px solid #ddd; object-fit: cover;">
                                @php $extraCount = $product->images ? $product->images->where('is_primary', false)->count() : $product->images()->where('is_primary', false)->count(); @endphp
                                @if($extraCount > 0)
                                    <span class="badge badge-info" style="position: absolute; bottom: 0; right: 0;">{{ $extraCount }}</span>
                                @endif
                            </div>
                        @else
                            <span class="badge badge-secondary">Chưa có ảnh</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ Str::limit($product->name, 30) }}</strong>
                    </td>
                    <td>{{ Str::limit($product->short_description ?? $product->description, 50) }}</td>
                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                    <td>{{ $product->publisher->name ?? 'N/A' }}</td>
                    <td>{{ $product->genre ?: 'Chưa chọn' }}</td>
                    <td>{{ $product->esrb ?? 'N/A' }}</td>
                    <td>{{ number_format($product->price, 0, ',', '.') }}đ</td>
                    <td>{{ $product->stock }}</td>
                    <td>
                        <a href="{{ route('admin.products.edit', $product) }}{{ request()->query() ? '?' . http_build_query(request()->query()) : '' }}" class="btn btn-warning btn-xs" title="Chỉnh sửa">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <form action="{{ route('admin.products.destroy', $product) }}{{ request()->query() ? '?' . http_build_query(request()->query()) : '' }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="page" value="{{ request()->get('page') ?? 1 }}">
                            <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Bạn chắc chắn muốn xóa?')" title="Xóa">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center text-muted">Không có sản phẩm nào</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        @php
            $lastPage = $products->lastPage();
            $currentPage = $products->currentPage();
            $pageNumbers = $products->getUrlRange(1, $lastPage);
        @endphp
        <nav aria-label="Phân trang sản phẩm">
            <ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap admin-pagination-numeric">
                @foreach ($pageNumbers as $page => $url)
                    <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</div>

<style>
    .table td {
        vertical-align: middle;
    }

    .admin-pagination-numeric .page-link {
        min-width: 44px;
        text-align: center;
    }

    .admin-pagination-numeric .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }

    .card-body.border-bottom {
        border-bottom: 1px solid rgba(0,0,0,.125);
    }

    .filter-actions .btn {
        min-width: 110px;
    }
</style>
@endsection
