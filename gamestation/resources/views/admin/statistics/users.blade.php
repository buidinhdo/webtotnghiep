@extends('admin.layout')

@section('title', 'Thống kê người dùng')
@section('page_title', 'Thống kê người dùng')
@section('breadcrumb', 'Thống kê người dùng')

@section('content')
<div class="card chart-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.statistics.users') }}" class="form-row align-items-end">
            <div class="form-group col-md-4 mb-0">
                <label for="start_date" class="font-weight-600">Từ ngày</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
            </div>
            <div class="form-group col-md-4 mb-0">
                <label for="end_date" class="font-weight-600">Đến ngày</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
            </div>
            <div class="form-group col-md-4 mb-0">
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter mr-2"></i>Lọc dữ liệu</button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4 align-items-stretch">
    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-blue">
            <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
            <div class="stat-content">
                <div class="stat-label">Đăng ký mới (Trong kỳ)</div>
                <div class="stat-value">{{ $userRegistrationData->sum('count') }}<span>thành viên</span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-green">
            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
            <div class="stat-content">
                <div class="stat-label">Tổng người dùng hoạt động</div>
                <div class="stat-value">{{ $activeUsersCount }}<span>tài khoản</span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-red">
            <div class="stat-icon"><i class="fas fa-user-slash"></i></div>
            <div class="stat-content">
                <div class="stat-label">Tổng người dùng bị khóa</div>
                <div class="stat-value">{{ $inactiveUsersCount }}<span>tài khoản</span></div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card chart-card">
            <div class="card-header border-0 pb-0">
                <h5 class="card-title mb-0">Số lượng tài khoản đăng ký mới hàng ngày</h5>
            </div>
            <div class="card-body pt-3">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="userRegistrationChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card chart-card">
    <div class="card-header border-0 pb-0">
        <h5 class="card-title mb-0">Top 10 khách hàng chi tiêu nhiều nhất trong kỳ</h5>
    </div>
    <div class="card-body pt-3 table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th width="60">HẠNG</th>
                    <th>HỌ VÀ TÊN</th>
                    <th>EMAIL</th>
                    <th class="text-center">SỐ ĐƠN THÀNH CÔNG</th>
                    <th class="text-right">TỔNG CHI TIÊU</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topCustomers as $idx => $customer)
                    <tr>
                        <td><span class="badge badge-primary px-2 py-1 font-weight-700">{{ $idx + 1 }}</span></td>
                        <td><strong>{{ $customer->name }}</strong></td>
                        <td>{{ $customer->email }}</td>
                        <td class="text-center">{{ $customer->total_orders ?? 0 }}</td>
                        <td class="text-right"><strong>{{ number_format($customer->total_spent ?? 0, 0, ',', '.') }}đ</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Không có giao dịch nào từ khách hàng trong khoảng thời gian này</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('extra_css')
<style>
    .stat-card {
        background: linear-gradient(135deg, #f5f7fa 0%, #fff 100%);
        border: 1px solid #e1e8f0;
        border-radius: 12px;
        padding: 24px;
        display: flex;
        gap: 16px;
        margin-bottom: 12px;
    }

    .stat-card-blue .stat-icon { color: #2563eb; background: #dbeafe; }
    .stat-card-green .stat-icon { color: #059669; background: #d1fae5; }
    .stat-card-red .stat-icon { color: #dc2626; background: #fee2e2; }

    .stat-icon {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        flex-shrink: 0;
    }

    .stat-label {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1;
    }

    .stat-value span {
        font-size: 14px;
        margin-left: 4px;
        color: #64748b;
    }

    .chart-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        background: #fff;
    }

    .chart-card .card-header {
        padding: 18px 20px;
    }

    .chart-card .card-title {
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
    }

    .chart-card .card-body {
        padding: 18px 20px;
    }

    .table thead th {
        border-top: none;
        border-bottom: 2px solid #e2e8f0;
        color: #64748b;
        font-size: 12px;
        padding: 12px 14px;
    }

    .table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
    }
</style>
@endsection

@section('extra_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const regCtx = document.getElementById('userRegistrationChart');
    if (regCtx) {
        new Chart(regCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($userRegistrationData->map(fn($r) => date('d/m', strtotime($r->date)))) !!},
                datasets: [{
                    label: 'Tài khoản đăng ký mới',
                    data: {!! json_encode($userRegistrationData->map(fn($r) => $r->count)) !!},
                    backgroundColor: '#86d6d1',
                    borderRadius: 6,
                    barThickness: 18
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }
});
</script>
@endsection
