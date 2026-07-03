@extends('admin.layout')

@section('title', 'Quản lý ESRB')
@section('page_title', 'Danh sách ESRB')
@section('breadcrumb', 'ESRB')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quản lý ESRB (Phân loại độ tuổi game)</h3>
        <div class="card-tools">
            <a href="{{ route('admin.esrb.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm ESRB
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Mã</th>
                    <th>Tên</th>
                    <th>Nhóm tuổi</th>
                    <th>Trạng thái</th>
                    <th width="150">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($esrbRatings as $rating)
                    <tr>
                        <td>{{ $rating->id }}</td>
                        <td><strong>{{ $rating->code }}</strong></td>
                        <td>{{ $rating->name }}</td>
                        <td>{{ $rating->age_group }}</td>
                        <td>
                            @if($rating->is_active)
                                <span class="badge badge-success">Hoạt động</span>
                            @else
                                <span class="badge badge-danger">Không hoạt động</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.esrb.edit', ['esrb' => $rating->id]) }}" class="btn btn-warning btn-xs">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                            <form action="{{ route('admin.esrb.destroy', ['esrb' => $rating->id]) }}" method="POST" style="display:inline;">
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
                        <td colspan="6" class="text-center text-muted py-4">Chưa có ESRB nào</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($esrbRatings->hasPages())
        <div class="card-footer">
            {{ $esrbRatings->links() }}
        </div>
    @endif
</div>
@endsection
