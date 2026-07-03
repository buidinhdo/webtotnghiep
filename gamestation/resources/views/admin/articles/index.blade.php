@extends('admin.layout')

@section('title', 'Quản lý bài viết')
@section('page_title', 'Quản lý bài viết')
@section('breadcrumb', 'Quản lý bài viết')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Danh sách bài viết</h3>
        <div class="card-tools">
            <a href="{{ route('admin.articles.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm bài viết
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($articles->count())
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Tác giả</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($articles as $article)
                    <tr>
                        <td>{{ ($articles->currentPage() - 1) * $articles->perPage() + $loop->iteration }}</td>
                        <td>
                            @if($article->image_path)
                                <img src="{{ asset($article->image_path) }}" alt="{{ $article->title }}" style="max-width: 100px; max-height: 100px; border-radius: 5px; border: 1px solid #ddd;">
                            @else
                                <span class="badge badge-secondary">Chưa có ảnh</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ Str::limit($article->title, 40) }}</strong><br>
                            <small class="text-muted">{{ Str::limit($article->excerpt ?? $article->content, 50) }}</small>
                        </td>
                        <td>{{ $article->author->name ?? 'Chưa xác định' }}</td>
                        <td>
                            @if($article->is_published)
                                <span class="badge badge-success">Đã xuất bản</span>
                            @else
                                <span class="badge badge-warning">Nháp</span>
                            @endif
                        </td>
                        <td>{{ $article->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.articles.destroy', $article) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn chắc chắn?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $articles->links() }}
        </div>
        @else
        <div class="alert alert-info">
            Chưa có bài viết nào. <a href="{{ route('admin.articles.create') }}">Thêm bài viết mới</a>
        </div>
        @endif
    </div>
</div>
@endsection
