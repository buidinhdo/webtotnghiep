@extends('admin.layout')

@section('title', 'Quản lý đánh giá')
@section('page_title', 'Danh sách đánh giá')
@section('breadcrumb', 'Đánh giá')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quản lý đánh giá sản phẩm</h3>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">STT</th>
                    <th>Sản phẩm</th>
                    <th>Người dùng</th>
                    <th>Đánh giá</th>
                    <th>Nội dung</th>
                    <th>Ngày tạo</th>
                    <th style="width: 120px">Hành động</th>
                    <th style="width: 120px">Xử lý</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                <tr>
                    <td>{{ $review->id }}</td>
                    <td>{{ $review->product->name ?? 'N/A' }}</td>
                    <td>{{ $review->user->name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge badge-warning">{{ $review->rating }}/5</span>
                    </td>
                    <td>{{ Str::limit($review->comment, 50) }}</td>
                    <td>{{ $review->created_at?->format('d/m/Y') }}</td>
                    <td>
                        @if(!$review->admin_reply)
                            <span class="badge badge-danger">Chưa phản hồi</span>
                        @else
                            <span class="badge badge-success">Đã phản hồi</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.reviews.show', $review) }}" class="btn btn-info btn-xs" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </a>
                        <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Bạn chắc chắn muốn xóa?')" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">Không có đánh giá nào</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $reviews->links() }}
    </div>
</div>
@endsection
