@extends('admin.layout')

@section('title', 'Quản lý banner')
@section('page_title', 'Danh sách banner')
@section('breadcrumb', 'Banner')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quản lý banner</h3>
        <div class="card-tools">
            <a href="{{ route('admin.banners.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm banner
            </a>
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">STT</th>
                    <th style="width: 150px">Ảnh</th>
                    <th>Tên file</th>
                    <th style="width: 180px">Thứ tự hiển thị</th>
                    <th style="width: 180px">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banners as $key => $banner)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>
                        <img src="{{ asset($banner->image_path) }}" alt="{{ basename($banner->image_path) }}" style="max-width: 100px; max-height: 100px; border-radius: 5px; border: 1px solid #ddd; object-fit: cover;">
                    </td>
                    <td>
                        <strong>{{ Str::limit(basename($banner->image_path), 50) }}</strong>
                    </td>
                    <td>
                        <form action="{{ route('admin.banners.updateOrder', $banner->id) }}" method="POST" class="d-flex align-items-center" style="gap: 8px;">
                            @csrf
                            @method('PUT')
                            <input
                                type="number"
                                name="order"
                                min="1"
                                value="{{ $banner->order_column > 0 ? $banner->order_column : ($key + 1) }}"
                                class="form-control form-control-sm"
                                style="width: 90px;"
                                required
                            >
                            <button type="submit" class="btn btn-info btn-sm">Lưu</button>
                        </form>
                    </td>
                    <td>
                        <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn btn-warning btn-sm" title="Sửa">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn chắc chắn muốn xóa?')" title="Xóa">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Không có banner nào</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .table td {
        vertical-align: middle;
    }
</style>
@endsection
