@extends('admin.layout')

@section('title', 'Chi tiết liên hệ')
@section('page_title', 'Chi tiết liên hệ')
@section('breadcrumb', 'Chi tiết liên hệ')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Nội dung liên hệ</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label><strong>Tên:</strong></label>
                    <p>{{ $contact->name }}</p>
                </div>
                <div class="form-group">
                    <label><strong>Email:</strong></label>
                    <p>{{ $contact->email }}</p>
                </div>
                <div class="form-group">
                    <label><strong>Chủ đề:</strong></label>
                    <p>{{ $contact->subject }}</p>
                </div>
                <div class="form-group">
                    <label><strong>Nội dung:</strong></label>
                    <div class=\"card bg-light\">
                        <div class=\"card-body\" style=\"white-space: pre-wrap; word-wrap: break-word; overflow-wrap: break-word; max-height: 400px; overflow-y: auto;\">
                            {{ $contact->message }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!$contact->admin_reply)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Phản hồi liên hệ</h3>
            </div>
            <form action="{{ route('admin.contacts.reply', $contact) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Phản hồi <span class="text-danger">*</span></label>
                        <textarea name="reply" class="form-control @error('reply') is-invalid @enderror" rows="4" required></textarea>
                        @error('reply')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Gửi phản hồi</button>
                </div>
            </form>
        </div>
        @else
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Phản hồi đã gửi</h3>
            </div>
            <div class="card-body">
                <div class=\"card bg-light mb-3\">
                    <div class=\"card-body\" style=\"white-space: pre-wrap; word-wrap: break-word; overflow-wrap: break-word; max-height: 400px; overflow-y: auto;\">
                        {{ $contact->admin_reply }}
                    </div>
                </div>
                <p class=\"text-muted text-sm\">Gửi lúc: {{ $contact->admin_replied_at?->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
