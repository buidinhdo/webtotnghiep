@extends('admin.layout')

@section('title', 'Chi tiết hội thoại')
@section('page_title', 'Hội thoại với ' . $user->name)
@section('breadcrumb', 'Chi tiết hội thoại')

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Direct Chat -->
        <div class="card card-primary card-outline direct-chat direct-chat-primary">
            <div class="card-header">
                <h3 class="card-title">Lịch sử hội thoại Chatbot</h3>
                <div class="card-tools">
                    <span class="badge badge-primary">{{ count($messages) }} tin nhắn</span>
                    <a href="{{ route('admin.chatbot.index') }}" class="btn btn-tool" title="Quay lại">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success m-2">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Conversations box -->
                <div class="direct-chat-messages" style="height: 400px; overflow-y: auto; padding: 15px; background-color: #f4f6f9;" id="adminChatContainer">
                    
                    @forelse($messages as $msg)
                        @if($msg->sender === 'user')
                            <!-- Message. Default to the left (User) -->
                            <div class="direct-chat-msg mb-4">
                                <div class="direct-chat-infos clearfix">
                                    <span class="direct-chat-name float-left">{{ $user->name }}</span>
                                    <span class="direct-chat-timestamp float-right text-muted">{{ $msg->created_at->format('H:i d/m/Y') }}</span>
                                </div>
                                <div class="bg-primary text-white p-3 rounded-lg d-inline-block shadow-sm" style="max-width: 80%; border-top-left-radius: 0 !important;">
                                    {!! nl2br(e($msg->message)) !!}
                                </div>
                            </div>
                        @elseif($msg->sender === 'bot')
                            <!-- Message. Left (Chatbot) -->
                            <div class="direct-chat-msg mb-4">
                                <div class="direct-chat-infos clearfix">
                                    <span class="direct-chat-name float-left text-info"><i class="fas fa-robot mr-1"></i> Trợ lý ảo (Chatbot)</span>
                                    <span class="direct-chat-timestamp float-right text-muted">{{ $msg->created_at->format('H:i d/m/Y') }}</span>
                                </div>
                                <div class="bg-light border text-dark p-3 rounded-lg d-inline-block shadow-sm" style="max-width: 80%; border-top-left-radius: 0 !important; font-size: 0.9rem;">
                                    {!! nl2br(e($msg->message)) !!}
                                </div>
                            </div>
                        @else
                            <!-- Message to the right (Admin) -->
                            <div class="direct-chat-msg right mb-4 text-right">
                                <div class="direct-chat-infos clearfix">
                                    <span class="direct-chat-name float-right text-success"><i class="fas fa-user-shield mr-1"></i> Quản trị viên</span>
                                    <span class="direct-chat-timestamp float-left text-muted">{{ $msg->created_at->format('H:i d/m/Y') }}</span>
                                </div>
                                <div class="bg-success text-white p-3 rounded-lg d-inline-block shadow-sm text-left" style="max-width: 80%; border-top-right-radius: 0 !important;">
                                    {!! nl2br(e($msg->message)) !!}
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="text-center py-5 text-muted">
                            Chưa có cuộc hội thoại nào.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Footer Reply box -->
            <div class="card-footer">
                <form action="{{ route('admin.chatbot.reply', $user) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="message" placeholder="Nhập nội dung phản hồi trực tiếp..." class="form-control" required autocomplete="off">
                        <span class="input-group-append">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i> Gửi phản hồi</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Client info sidebar -->
    <div class="col-md-4">
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">Thông tin khách hàng</h3>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Tên khách hàng:</b> <a class="float-right text-dark font-weight-bold">{{ $user->name }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Email:</b> <a class="float-right text-muted">{{ $user->email }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Số điện thoại:</b> <a class="float-right text-muted">{{ $user->phone ?? 'Chưa cập nhật' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Địa chỉ:</b> <a class="float-right text-muted">{{ $user->address ?? 'Chưa cập nhật' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Ngày đăng ký:</b> <a class="float-right text-muted">{{ $user->created_at?->format('d/m/Y') }}</a>
                    </li>
                </ul>
                <form action="{{ route('admin.chatbot.destroy', $user) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa hội thoại?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fas fa-trash mr-1"></i> Xóa lịch sử hội thoại
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Auto scroll to bottom of chat
        var chatBox = document.getElementById("adminChatContainer");
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    });
</script>
@endsection
