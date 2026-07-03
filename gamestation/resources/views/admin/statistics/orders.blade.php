@extends('admin.layout')

@section('title', 'Thống kê đơn hàng')
@section('page_title', 'Thống kê đơn hàng')
@section('breadcrumb', 'Thống kê đơn hàng')

@section('content')
<div class="card chart-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.statistics.orders') }}" class="form-row align-items-end">
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
            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-content">
                <div class="stat-label">Tổng số đơn hàng</div>
                <div class="stat-value">{{ $totalOrders }}<span>đơn</span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-green">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <div class="stat-label">Tỷ lệ thành công</div>
                <div class="stat-value">{{ number_format($successRate, 1) }}<span>%</span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-red">
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
            <div class="stat-content">
                <div class="stat-label">Tỷ lệ hủy đơn</div>
                <div class="stat-value">{{ number_format($cancelRate, 1) }}<span>%</span></div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-5 d-flex">
        <div class="card chart-card w-100 h-100">
            <div class="card-header border-0 pb-0">
                <h5 class="card-title mb-0">Tỷ lệ trạng thái đơn hàng</h5>
            </div>
            <div class="card-body pt-3 d-flex align-items-center justify-content-center">
                <div class="chart-container" style="position: relative; height: 280px; width: 100%;">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-7 d-flex">
        <div class="card chart-card w-100 h-100">
            <div class="card-header border-0 pb-0">
                <h5 class="card-title mb-0">Sản phẩm bán chạy nhất trong kỳ</h5>
            </div>
            <div class="card-body pt-3">
                <div class="top-products-list">
                    @forelse($topProducts as $idx => $product)
                        <div class="top-product-item d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="product-rank">{{ $idx + 1 }}</div>
                                <div class="top-product-thumb">
                                    @if($product->primaryImage && $product->primaryImage->image_path)
                                        <img src="{{ asset($product->primaryImage->image_path) }}" alt="{{ $product->name }}">
                                    @else
                                        <i class="fas fa-box-open"></i>
                                    @endif
                                </div>
                                <div class="top-product-meta">
                                    <span class="product-name font-weight-600">{{ Str::limit($product->name, 45) }}</span>
                                    <div class="product-price">{{ number_format($product->total_revenue, 0, ',', '.') }}đ doanh thu</div>
                                </div>
                            </div>
                            <div class="sold-badge">{{ $product->total_sold }} đã bán</div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">Chưa có sản phẩm nào bán ra trong khoảng thời gian này</div>
                    @endforelse
                </div>
            </div>
        </div>
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

    .top-products-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .top-product-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 12px;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .product-rank {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #2563eb;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 12px;
        flex-shrink: 0;
    }

    .top-product-thumb {
        width: 48px;
        height: 48px;
        border-radius: 6px;
        overflow: hidden;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .top-product-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .top-product-meta {
        flex-grow: 1;
    }

    .product-name {
        font-size: 14px;
        color: #1e293b;
        display: block;
    }

    .product-price {
        font-size: 12px;
        color: #64748b;
    }

    .sold-badge {
        background: #10b981;
        color: #fff;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        flex-shrink: 0;
    }
</style>
@endsection

@section('extra_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusCtx = document.getElementById('orderStatusChart');
    if (statusCtx) {
        const statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
        const statusMap = {
            'pending': 'Chờ xử lý',
            'processing': 'Đang xử lý',
            'shipped': 'Đang giao',
            'completed': 'Hoàn thành',
            'cancelled': 'Đã hủy'
        };

        const rawStats = @json($orderStatusStats);
        const data = statuses.map(s => rawStats[s] ? rawStats[s].count : 0);
        const labels = statuses.map(s => statusMap[s]);

        new Chart(statusCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ['#ff7b9c', '#4aa3ff', '#ffd26a', '#7bd0c1', '#c68bff'],
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
