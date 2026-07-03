<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        // Tổng doanh thu hôm nay, tháng này, năm này
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        $thisYear = now()->startOfYear();

        $revenueToday = Order::where('status', 'completed')
            ->whereDate('completed_at', today())
            ->sum('total');

        $revenueMonth = Order::where('status', 'completed')
            ->whereBetween('completed_at', [$thisMonth, now()])
            ->sum('total');

        $revenueYear = Order::where('status', 'completed')
            ->whereBetween('completed_at', [$thisYear, now()])
            ->sum('total');

        // Số đơn hàng theo trạng thái
        $ordersStats = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $ordersPending = Order::where('status', 'pending')->count();
        $ordersProcessing = Order::where('status', 'processing')->count();
        $ordersShipped = Order::where('status', 'shipped')->count();
        $ordersCompleted = Order::where('status', 'completed')->count();
        $ordersCancelled = Order::where('status', 'cancelled')->count();

        // Lấy 5 đơn gần nhất (mới -> cũ) để hiển thị trên dashboard như giao diện cũ
        $recentOrders = Order::with('user', 'items.product')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Sản phẩm bán chạy (không giới hạn top 5, lấy toàn bộ theo số lượng đã bán)
        $topProducts = Product::with('images')
            ->withSum('orderItems as total_sold', 'quantity')
            ->orderByDesc('total_sold')
            ->get()
            ->filter(fn ($product) => (int) ($product->total_sold ?? 0) > 0)
            ->values()
            ->map(function($product) {
                // Lấy ảnh primary, hoặc ảnh đầu tiên nếu không có primary
                $product->primaryImage = $product->images->where('is_primary', true)->first() 
                    ?? $product->images->first();
                return $product;
            });

        // Thống kê chung
        $totalProducts = Product::count();
        $totalCustomers = User::where('is_admin', false)->count();
        $totalActiveCustomers = User::where('is_admin', false)->where('is_active', true)->count();
        $totalOrders = Order::count();

        // Dữ liệu biểu đồ doanh thu theo ngày trong 7 ngày gần nhất
        $chartStart = now()->subDays(6)->startOfDay();
        $chartEnd = now()->endOfDay();

        $chartRevenueRows = Order::where('status', 'completed')
            ->whereBetween('completed_at', [$chartStart, $chartEnd])
            ->selectRaw('DATE(completed_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue', 'date');

        $chartOrderRows = Order::whereBetween('created_at', [$chartStart, $chartEnd])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders_count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('orders_count', 'date');

        $chartCustomerRows = User::where('is_admin', false)
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as customers_count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('customers_count', 'date');

        $chartLabels = [];
        $chartRevenue = [];
        $chartOrders = [];
        $chartCustomers = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $chartLabels[] = now()->subDays($i)->format('d/m');
            $chartRevenue[] = (float) ($chartRevenueRows[$date] ?? 0);
            $chartOrders[] = (int) ($chartOrderRows[$date] ?? 0);
            $chartCustomers[] = (int) ($chartCustomerRows[$date] ?? 0);
        }

        // Dữ liệu biểu đồ doanh thu theo danh mục (chỉ PS4, PS5, Nintendo Switch)
        $categoryRevenue = $this->buildCategoryRevenueData();

        return view('admin.dashboard', [
            'revenueToday' => $revenueToday,
            'revenueMonth' => $revenueMonth,
            'revenueYear' => $revenueYear,
            'ordersPending' => $ordersPending,
            'ordersProcessing' => $ordersProcessing,
            'ordersShipped' => $ordersShipped,
            'ordersCompleted' => $ordersCompleted,
            'ordersCancelled' => $ordersCancelled,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'totalProducts' => $totalProducts,
            'totalCustomers' => $totalCustomers,
            'totalActiveCustomers' => $totalActiveCustomers,
            'totalOrders' => $totalOrders,
            'chartLabels' => $chartLabels,
            'chartRevenue' => $chartRevenue,
            'chartOrders' => $chartOrders,
            'chartCustomers' => $chartCustomers,
            'ordersStats' => $ordersStats,
            'categoryRevenue' => $categoryRevenue,
        ]);
    }

    public function getTopProducts()
    {
        $topProducts = Product::with('images')
            ->withSum('orderItems as total_sold', 'quantity')
            ->orderByDesc('total_sold')
            ->get()
            ->filter(fn ($product) => (int) ($product->total_sold ?? 0) > 0)
            ->values()
            ->map(function($product) {
                $product->primaryImage = $product->images->where('is_primary', true)->first() 
                    ?? $product->images->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'total_sold' => $product->total_sold,
                    'image_path' => $product->primaryImage?->image_path,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $topProducts,
        ]);
    }

    public function getCategoryRevenue()
    {
        $categoryRevenue = $this->buildCategoryRevenueData();

        return response()->json([
            'success' => true,
            'data' => $categoryRevenue,
        ]);
    }

    private function buildCategoryRevenueData()
    {
        $targetLabels = ['PlayStation 4', 'PlayStation 5', 'Nintendo Switch'];

        $groupExpr = "CASE
            WHEN LOWER(categories.slug) = 'ps4'
              OR LOWER(categories.name) LIKE '%ps4%'
              OR LOWER(categories.name) LIKE '%playstation 4%'
            THEN 'PlayStation 4'
            WHEN LOWER(categories.slug) = 'ps5'
              OR LOWER(categories.name) LIKE '%ps5%'
              OR LOWER(categories.name) LIKE '%playstation 5%'
            THEN 'PlayStation 5'
            WHEN LOWER(categories.slug) = 'switch'
              OR LOWER(categories.name) LIKE '%switch%'
              OR LOWER(categories.name) LIKE '%nintendo%'
            THEN 'Nintendo Switch'
            ELSE NULL
        END";

        $revenueByGroup = Order::query()
            ->where('orders.status', 'completed')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw("{$groupExpr} as category_group")
            ->selectRaw('SUM(COALESCE(order_items.total, order_items.price * order_items.quantity)) as revenue')
            ->groupBy(DB::raw($groupExpr))
            ->get()
            ->filter(fn ($row) => !empty($row->category_group))
            ->pluck('revenue', 'category_group');

        return collect($targetLabels)->map(function ($label) use ($revenueByGroup) {
            return [
                'name' => $label,
                'revenue' => (int) round((float) ($revenueByGroup[$label] ?? 0)),
            ];
        })->values();
    }
}
