@extends('admin.layout')

@section('title', 'Dashboard')
@section('page_title', 'Bảng điều khiển')
@section('breadcrumb', 'Bảng điều khiển')

@section('content')
@php
    $statusMap = [
        'pending' => ['label' => 'Chờ xử lý', 'color' => 'warning'],
        'processing' => ['label' => 'Đang xử lý', 'color' => 'info'],
        'shipped' => ['label' => 'Đang giao', 'color' => 'primary'],
        'completed' => ['label' => 'Hoàn thành', 'color' => 'success'],
        'cancelled' => ['label' => 'Hủy', 'color' => 'danger'],
    ];

    $statusCounts = [
        'pending' => $ordersPending,
        'processing' => $ordersProcessing,
        'shipped' => $ordersShipped,
        'completed' => $ordersCompleted,
        'cancelled' => $ordersCancelled,
    ];
@endphp

<div class="row mb-4 align-items-stretch">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-blue">
            <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="stat-content">
                <div class="stat-label">Doanh thu hôm nay</div>
                <div class="stat-value">{{ number_format($revenueToday, 0, ',', '.') }}<span>đ</span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-green">
            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-content">
                <div class="stat-label">Tổng đơn hàng</div>
                <div class="stat-value">{{ $totalOrders }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-purple">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-content">
                <div class="stat-label">Người dùng hoạt động</div>
                <div class="stat-value">{{ $totalActiveCustomers }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-orange">
            <div class="stat-icon"><i class="fas fa-cube"></i></div>
            <div class="stat-content">
                <div class="stat-label">Tổng sản phẩm</div>
                <div class="stat-value">{{ $totalProducts }}</div>
            </div>
        </div>
    </div>
</div>



<div class="row mb-4 align-items-stretch">
    <div class="col-lg-8 d-flex">
        <div class="card chart-card h-100 w-100">
            <div class="card-header border-0 pb-0 revenue-chart-header">
                <h5 class="card-title mb-0">Doanh Thu,Đơn Hàng,Khách Hàng</h5>
                <small class="text-muted chart-subtitle">Thống kê 7 ngày gần đây</small>
            </div>
            <div class="card-body pt-3">
                <div class="chart-container" style="position: relative; height: 320px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 d-flex">
        <div class="card chart-card w-100 h-100">
            <div class="card-header border-0 pb-0">
                <h5 class="card-title mb-0">Trạng thái đơn hàng</h5>
                <small class="text-muted" style="visibility: hidden; display: block;">.</small>
            </div>
            <div class="card-body pt-3 d-flex align-items-center justify-content-center h-100">
                <div class="chart-container" style="position: relative; height: 320px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-6 d-flex">
        <div class="card chart-card dashboard-panel dashboard-category-panel w-100">
            <div class="card-header border-0 pb-0">
                <h5 class="card-title mb-0">Sản phẩm theo danh mục</h5>
            </div>
            <div class="card-body pt-3 dashboard-panel-body">
                <div class="chart-container dashboard-category-chart-wrap">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 d-flex">
        <div class="card chart-card dashboard-panel dashboard-top-panel w-100">
            <div class="card-header border-0 pb-0">
                <h5 class="card-title mb-0">Sản phẩm bán chạy</h5>
            </div>
            <div class="card-body pt-3 dashboard-panel-body d-flex flex-column">
                <div class="top-products-list" id="topProductsList">
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
                                    <a href="{{ route('products.show', $product) }}" class="product-name">{{ 
                                        Str::limit($product->name, 60) }}</a>
                                    <div class="product-price">{{ number_format($product->price, 0, ',', '.') }}đ</div>
                                </div>
                            </div>
                            <div class="sold-badge">{{ $product->total_sold ?? 0 }} đã bán</div>
                        </div>
                    @empty
                        <div class="text-center text-muted">Chưa có sản phẩm bán chạy</div>
                    @endforelse
                </div>
                </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card chart-card">
            <div class="card-header border-0 pb-0">
                <h5 class="card-title mb-0">Đơn hàng gần đây</h5>
            </div>
            <div class="card-body pt-3 table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr class="table-action-row">
                            <th colspan="4"></th>
                            <th class="text-right">
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                            </th>
                        </tr>
                        <tr>
                            <th width="80">MÃ ĐH</th>
                            <th>KHÁCH HÀNG</th>
                            <th>NGÀY</th>
                            <th width="120">TRẠNG THÁI</th>
                            <th width="130" class="text-right">TỔNG CỘNG</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td><strong>{{ $order->id }}</strong></td>
                                <td>{{ $order->user->name ?? 'Khách vãng lai' }}</td>
                                <td>{{ $order->created_at?->format('d.m.Y') }}</td>
                                <td>
                                    <span class="badge badge-{{ $order->status == 'completed' ? 'success' : ($order->status == 'cancelled' ? 'danger' : 'warning') }}">
                                        {{ $order->status_label }}
                                    </span>
                                </td>
                                <td class="text-right"><strong>{{ number_format($order->total ?? $order->total_amount ?? 0, 0, ',', '.') }}đ</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Không có đơn hàng nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
    .stat-card-purple .stat-icon { color: #9333ea; background: #ede9fe; }
    .stat-card-orange .stat-icon { color: #ea580c; background: #fed7aa; }

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

    .revenue-chart-header {
        position: relative;
        min-height: 54px;
    }

    .revenue-chart-header .chart-subtitle {
        display: block;
        text-align: center;
        margin-top: 6px;
        font-size: 14px;
    }

    .chart-card .card-title {
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
    }

    .chart-card .card-body {
        padding: 18px 20px;
    }

    .dashboard-panel {
        height: 360px;
        min-height: 360px;
        max-height: 360px;
        overflow: hidden;
    }

    .dashboard-panel-body {
        height: calc(360px - 72px);
        min-height: 0;
        overflow: hidden;
    }

    .dashboard-category-chart-wrap {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dashboard-category-chart-wrap canvas {
        width: 100% !important;
        height: 100% !important;
        max-width: 100%;
        display: block;
    }

    .top-products-list {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 8px;
        height: 100%;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 8px;
    }

    .top-product-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        border-bottom: 1px solid #f1f5f9;
        min-height: 56px;
        position: relative;
        padding-right: 120px; /* reserve space for absolute badge */
    }

    /* Ensure left content shrinks and doesn't flow under the absolute badge */
    .top-product-item > .d-flex.align-items-center {
        flex: 1 1 auto;
        min-width: 0;
    }

    .top-product-item:last-child {
        border-bottom: 0;
    }

    .product-rank {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        background: linear-gradient(180deg, #2b7cff 0%, #0d47a1 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .top-product-thumb {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        overflow: hidden;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .top-product-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .top-product-thumb i {
        color: #94a3b8;
        font-size: 18px;
    }

    .top-product-meta {
        flex: 1 1 auto;
        min-width: 0;
        padding-right: 10px;
    }

    .product-name {
        color: #065fd4;
        font-size: 14px;
        font-weight: 600;
        display: block;
        width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .product-price {
        color: #64748b;
        font-size: 13px;
    }

    .sold-badge {
        background: #34d399; /* light green */
        color: #ffffff;
        padding: 0 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.04);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 28px;
        min-width: 44px;
        text-align: center;
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
    }

    .table thead th {
        border-top: none;
        border-bottom: 2px solid #e2e8f0;
        color: #64748b;
        font-size: 12px;
        padding: 12px 14px;
    }

    .table-action-row th {
        border-bottom: none !important;
        padding-top: 0;
        padding-bottom: 8px;
    }

    .table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
    }

    @media (max-width: 991px) {
        .dashboard-panel,
        .dashboard-panel-body {
            height: auto;
            min-height: 320px;
            max-height: none;
        }

        .top-products-list {
            max-height: 260px;
        }
    }
</style>
@endsection

@section('extra_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const revenueLabels = @json($chartLabels);
    const revenueValues = @json($chartRevenue);
    const orderValues = @json($chartOrders);
    const customerValues = @json($chartCustomers);

    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: revenueLabels,
                datasets: [
                    {
                        label: 'Doanh thu (VND)',
                        data: revenueValues,
                        backgroundColor: '#46c7ec',
                        borderRadius: 6,
                        barThickness: 16,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Số đơn hàng',
                        data: orderValues,
                        backgroundColor: '#f4a261',
                        borderRadius: 6,
                        barThickness: 16,
                        yAxisID: 'y1'
                    },
                    {
                        label: 'Số khách hàng mới',
                        data: customerValues,
                        backgroundColor: '#86d6d1',
                        borderRadius: 6,
                        barThickness: 16,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: true, position: 'top' } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('vi-VN') + ' đ';
                            }
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const statusLabels = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
        const statusRoutes = @json(route('admin.orders.index'));

        const statusChart = new Chart(statusCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: ['Chờ xử lý', 'Đang xử lý', 'Đang giao', 'Hoàn thành', 'Hủy'],
                datasets: [{
                    data: @json(array_values($statusCounts)),
                    backgroundColor: ['#ff7b9c', '#4aa3ff', '#ffd26a', '#7bd0c1', '#c68bff'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                onClick: function(event, elements) {
                    if (!elements.length) return;

                    const first = elements[0];
                    const status = statusLabels[first.index];

                    if (!status) return;

                    const url = new URL(statusRoutes, window.location.origin);
                    url.searchParams.set('status', status);
                    window.location.href = url.toString();
                }
            }
        });

        statusCtx.style.cursor = 'pointer';
    }

    const categoryRaw = @json($categoryRevenue);
    const findValue = function(keywords) {
        const found = categoryRaw.find(function(item) {
            const name = String(item.name || '').toLowerCase();
            return keywords.some(function(k) { return name.includes(k); });
        });
        return found ? Number(found.revenue || 0) : 0;
    };

    const categoryCodes = ['ps4', 'ps5', 'switch'];
    const categoryProductsUrl = @json(route('admin.products.index'));

    const categoryLabels = ['PlayStation 4', 'PlayStation 5', 'Nintendo Switch'];
    const categoryValues = [
        findValue(['ps4', 'playstation 4']),
        findValue(['ps5', 'playstation 5']),
        findValue(['nintendo switch', 'switch'])
    ];

    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        const categoryChart = new Chart(categoryCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryValues,
                    backgroundColor: ['#4aa3ff', '#ff7b9c', '#ffd26a'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                onClick: function(event, elements) {
                    if (!elements.length) return;

                    const category = categoryCodes[elements[0].index];
                    if (!category) return;

                    const url = new URL(categoryProductsUrl, window.location.origin);
                    url.searchParams.set('category', category);
                    window.location.href = url.toString();
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'right'
                    }
                }
            }
        });

        categoryCtx.style.cursor = 'pointer';
    }

    // Safety lock: do not allow any leftover scripts to auto-refresh this page section.
    if (window.updateTopProducts) window.updateTopProducts = function() {};
    if (window.updateCategoryRevenue) window.updateCategoryRevenue = function() {};
});
</script>
 
@endsection
