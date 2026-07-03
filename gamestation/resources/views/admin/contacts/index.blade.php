@extends('admin.layout')

@section('title', 'Quản lý liên hệ')
@section('page_title', 'Danh sách liên hệ')
@section('breadcrumb', 'Liên hệ')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quản lý liên hệ từ khách hàng</h3>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">STT</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Chủ đề</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th style="width: 150px">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contacts as $contact)
                <tr>
                    <td>{{ $contact->id }}</td>
                    <td>{{ $contact->name }}</td>
                    <td>{{ $contact->email }}</td>
                    <td>{{ Str::limit($contact->subject, 30) }}</td>
                    <td>
                        @php
                            $statusLabels = [
                                'pending'    => 'Chờ phản hồi',
                                'replied'    => 'Đã phản hồi',
                                'closed'     => 'Đã đóng',
                                'spam'       => 'Spam',
                            ];
                            $statusColors = [
                                'pending'    => 'warning',
                                'replied'    => 'success',
                                'closed'     => 'secondary',
                                'spam'       => 'danger',
                            ];
                            $label = $statusLabels[$contact->status] ?? ucfirst($contact->status);
                            $color = $statusColors[$contact->status] ?? 'secondary';
                        @endphp
                        <span class="badge badge-{{ $color }}">{{ $label }}</span>
                    </td>
                    <td>{{ $contact->created_at?->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('admin.contacts.show', $contact) }}" class="btn btn-info btn-xs">
                            <i class="fas fa-eye"></i>
                        </a>
                        <form action="{{ route('admin.contacts.destroy', $contact) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Bạn chắc chắn muốn xóa?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">Không có liên hệ nào</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $contacts->links() }}
    </div>
</div>
@endsection
