@extends('admin.layout')

@section('title', 'Thống kê doanh thu')
@section('page_title', 'Thống kê doanh thu')
@section('breadcrumb', 'Thống kê doanh thu')

@section('content')
<div class="card chart-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.statistics.revenue') }}" id="filterForm" class="form-row align-items-end">
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
            <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="stat-content">
                <div class="stat-label">Tổng doanh thu</div>
                <div class="stat-value">{{ number_format($totalRevenue, 0, ',', '.') }}<span>đ</span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-green">
            <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
            <div class="stat-content">
                <div class="stat-label">Tổng số đơn hàng</div>
                <div class="stat-value">{{ $totalOrders }}<span>đơn</span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-purple">
            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
            <div class="stat-content">
                <div class="stat-label">Giá trị đơn trung bình</div>
                <div class="stat-value">{{ number_format($averageOrderValue, 0, ',', '.') }}<span>đ</span></div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-8 d-flex">
        <div class="card chart-card w-100 h-100">
            <div class="card-header border-0 pb-0">
                <h5 class="card-title mb-0">Biểu đồ doanh thu hàng ngày</h5>
            </div>
            <div class="card-body pt-3">
                <div class="chart-container" style="position: relative; height: 320px;">
                    <canvas id="dailyRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 d-flex">
        <div class="card chart-card w-100 h-100">
            <div class="card-header border-0 pb-0">
                <h5 class="card-title mb-0">Phân bổ theo hệ máy</h5>
            </div>
            <div class="card-body pt-3 d-flex align-items-center justify-content-center">
                <div class="chart-container" style="position: relative; height: 260px; width: 100%;">
                    <canvas id="categoryRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card chart-card">
    <div class="card-header border-0 pb-0">
        <h5 class="card-title mb-0">Chi tiết doanh thu theo ngày</h5>
    </div>
    <div class="card-body pt-3 table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>NGÀY</th>
                    <th class="text-center">SỐ ĐƠN THÀNH CÔNG</th>
                    <th class="text-right">DOANH THU</th>
                </tr>
            </thead>
            <tbody>
                @forelse($revenueData as $row)
                    <tr>
                        <td><strong>{{ date('d/m/Y', strtotime($row->date)) }}</strong></td>
                        <td class="text-center">{{ $row->orders_count }}</td>
                        <td class="text-right"><strong>{{ number_format($row->revenue, 0, ',', '.') }}đ</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">Không có dữ liệu doanh thu trong khoảng thời gian này</td>
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
    .stat-card-purple .stat-icon { color: #9333ea; background: #ede9fe; }

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
    // Daily Revenue Chart
    const dailyCtx = document.getElementById('dailyRevenueChart');
    if (dailyCtx) {
        const ctx = dailyCtx.getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($revenueData->map(fn($r) => date('d/m', strtotime($r->date)))) !!},
                datasets: [{
                    label: 'Doanh thu (VND)',
                    data: {!! json_encode($revenueData->map(fn($r) => (float)$r->revenue)) !!},
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#2563eb',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 12, weight: '600' },
                        bodyFont: { size: 13 },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + context.parsed.y.toLocaleString('vi-VN') + ' đ';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: { size: 10 }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e2e8f0',
                            borderDash: [5, 5]
                        },
                        ticks: {
                            color: '#64748b',
                            font: { size: 10 },
                            callback: function(value) {
                                return value.toLocaleString('vi-VN') + ' đ';
                            }
                        }
                    }
                }
            }
        });
    }

    // Category Distribution Chart
    const catCtx = document.getElementById('categoryRevenueChart');
    if (catCtx) {
        const catLabels = {!! json_encode(array_keys($categoryRevenue->toArray())) !!};
        const catValues = {!! json_encode(array_values($categoryRevenue->toArray())) !!};

        new Chart(catCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catValues,
                    backgroundColor: ['#4aa3ff', '#ff7b9c', '#ffd26a', '#a78bfa', '#34d399'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 10 }
                    }
                }
            }
        });
    }
});
</script>
@endsection
