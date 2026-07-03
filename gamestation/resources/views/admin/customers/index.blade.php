@extends('admin.layout')

@section('title', 'Quản lý khách hàng')
@section('page_title', 'Danh sách khách hàng')
@section('breadcrumb', 'Khách hàng')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quản lý khách hàng</h3>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">STT</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Điện thoại</th>
                    <th>Ngày tạo</th>
                    <th style="width: 150px">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td>{{ ($customers->currentPage() - 1) * $customers->perPage() + $loop->iteration }}</td>
                    <td><strong>{{ $customer->name }}</strong></td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone ?? 'N/A' }}</td>
                    <td>{{ $customer->created_at?->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-info btn-xs">
                            <i class="fas fa-eye"></i>
                        </a>
                        <form action="{{ route('admin.customers.toggleStatus', $customer) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-{{ $customer->is_active ? 'success' : 'danger' }} btn-xs" title="{{ $customer->is_active ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}">
                                <i class="fas fa-{{ $customer->is_active ? 'unlock' : 'lock' }}"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Không có khách hàng nào</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $customers->links() }}
    </div>
</div>
@endsection
