@extends('admin.layout')

@section('title', 'Quản lý nhà phát hành')
@section('page_title', 'Danh sách nhà phát hành')
@section('breadcrumb', 'Nhà phát hành')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Danh sách nhà phát hành</h3>
        <div class="card-tools">
            <a href="{{ route('admin.publishers.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm nhà phát hành
            </a>
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên</th>
                    <th>Slug</th>
                    <th style="width: 160px">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($publishers as $publisher)
                <tr>
                    <td>{{ $publisher->id }}</td>
                    <td>{{ $publisher->name }}</td>
                    <td>{{ $publisher->slug }}</td>
                    <td>
                        <a href="{{ route('admin.publishers.edit', $publisher) }}?page={{ request()->get('page') ?? 1 }}" class="btn btn-warning btn-xs">Sửa</a>
                        <form action="{{ route('admin.publishers.destroy', $publisher) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="page" value="{{ request()->get('page') ?? 1 }}">
                            <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Bạn chắc chắn muốn xóa?')">Xóa</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center">Không có nhà phát hành</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        @php
            $lastPage = $publishers->lastPage();
            $currentPage = $publishers->currentPage();
            $pageNumbers = $publishers->getUrlRange(1, $lastPage);
        @endphp
        <nav aria-label="Phân trang nhà phát hành">
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
</style>
@endsection
