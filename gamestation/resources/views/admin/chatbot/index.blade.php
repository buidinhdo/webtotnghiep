@extends('admin.layout')

@section('title', 'Quản lý Chatbot')
@section('page_title', 'Hội thoại Chatbot')
@section('breadcrumb', 'Chatbot')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Danh sách khách hàng đang hoạt động trên Chatbot</h3>
    </div>
    <div class="card-body table-responsive">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th style="width: 50px">ID</th>
                    <th>Khách hàng</th>
                    <th>Email</th>
                    <th>Tin nhắn cuối</th>
                    <th>Người gửi cuối</th>
                    <th>Tổng tin nhắn</th>
                    <th>Hoạt động cuối</th>
                    <th style="width: 150px">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->latestMessage)
                            <span class="text-muted">{{ Str::limit($user->latestMessage->message, 50) }}</span>
                        @else
                            <em class="text-muted">Chưa có tin nhắn</em>
                        @endif
                    </td>
                    <td>
                        @if($user->latestMessage)
                            @if($user->latestMessage->sender === 'user')
                                <span class="badge badge-primary">Khách hàng</span>
                            @elseif($user->latestMessage->sender === 'admin')
                                <span class="badge badge-success">Admin</span>
                            @else
                                <span class="badge badge-info">Chatbot</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ $user->chatbot_messages_count }}</span>
                    </td>
                    <td>{{ $user->latestMessage ? $user->latestMessage->created_at->format('H:i d/m/Y') : '-' }}</td>
                    <td>
                        <a href="{{ route('admin.chatbot.show', $user) }}" class="btn btn-info btn-xs" title="Xem hội thoại">
                            <i class="fas fa-eye mr-1"></i> Xem & Trả lời
                        </a>
                        <form action="{{ route('admin.chatbot.destroy', $user) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Bạn chắc chắn muốn xóa toàn bộ lịch sử chat với khách hàng này?')" title="Xóa hội thoại">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        Không có cuộc hội thoại chatbot nào được tìm thấy.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="card-footer clearfix">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
