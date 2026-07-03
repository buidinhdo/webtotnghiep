@extends('admin.layout')

@section('title', 'Quản lý thể loại')
@section('page_title', 'Danh sách thể loại')
@section('breadcrumb', 'Thể loại')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quản lý thể loại game/sản phẩm</h3>
        <div class="card-tools">
            <a href="{{ route('admin.genres.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm thể loại
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên thể loại</th>
                    <th>Mô tả</th>
                    <th>Trạng thái</th>
                    <th width="150">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($genres as $genre)
                    <tr>
                        <td>{{ $genre->id }}</td>
                        <td>{{ $genre->name }}</td>
                        <td>{{ Str::limit($genre->description, 50) }}</td>
                        <td>
                            @if($genre->is_active)
                                <span class="badge badge-success">Hoạt động</span>
                            @else
                                <span class="badge badge-danger">Không hoạt động</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.genres.edit', $genre) }}" class="btn btn-warning btn-xs">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                            <form action="{{ route('admin.genres.destroy', $genre) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Bạn chắc chắn muốn xóa?')">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Chưa có thể loại nào</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($genres->hasPages())
        <div class="card-footer">
            {{ $genres->links() }}
        </div>
    @endif
</div>
@endsection
