<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function revenue(Request $request)
    {
        $startDate = $request->input('start_date') ? now()->parse($request->input('start_date'))->startOfDay() : now()->subDays(30)->startOfDay();
        $endDate = $request->input('end_date') ? now()->parse($request->input('end_date'))->endOfDay() : now()->endOfDay();

        $revenueData = Order::where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->selectRaw('DATE(completed_at) as date, SUM(total) as revenue, COUNT(*) as orders_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalRevenue = $revenueData->sum('revenue');
        $totalOrders = $revenueData->sum('orders_count');
        $averageOrderValue = $totalOrders > 0 ? ($totalRevenue / $totalOrders) : 0;

        // Group by category to show category revenue in this period
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
            ELSE 'Khác'
        END";

        $categoryRevenue = Order::query()
            ->where('orders.status', 'completed')
            ->whereBetween('orders.completed_at', [$startDate, $endDate])
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw("{$groupExpr} as category_group")
            ->selectRaw('SUM(COALESCE(order_items.total, order_items.price * order_items.quantity)) as revenue')
            ->groupBy(DB::raw($groupExpr))
            ->get()
            ->pluck('revenue', 'category_group');

        return view('admin.statistics.revenue', compact(
            'revenueData',
            'totalRevenue',
            'totalOrders',
            'averageOrderValue',
            'startDate',
            'endDate',
            'categoryRevenue'
        ));
    }

    public function orders(Request $request)
    {
        $startDate = $request->input('start_date') ? now()->parse($request->input('start_date'))->startOfDay() : now()->subDays(30)->startOfDay();
        $endDate = $request->input('end_date') ? now()->parse($request->input('end_date'))->endOfDay() : now()->endOfDay();

        $orderStatusStats = Order::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count, SUM(total) as total_value')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $completedOrdersCount = $orderStatusStats['completed']->count ?? 0;
        $cancelledOrdersCount = $orderStatusStats['cancelled']->count ?? 0;

        $successRate = $totalOrders > 0 ? ($completedOrdersCount / $totalOrders) * 100 : 0;
        $cancelRate = $totalOrders > 0 ? ($cancelledOrdersCount / $totalOrders) * 100 : 0;

        // Top selling products in this period
        $topProducts = Product::with('primaryImage')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.completed_at', [$startDate, $endDate])
            ->select('products.*')
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            ->selectRaw('SUM(order_items.total) as total_revenue')
            ->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->take(10)
            ->get();

        return view('admin.statistics.orders', compact(
            'orderStatusStats',
            'totalOrders',
            'successRate',
            'cancelRate',
            'startDate',
            'endDate',
            'topProducts'
        ));
    }

    public function users(Request $request)
    {
        $startDate = $request->input('start_date') ? now()->parse($request->input('start_date'))->startOfDay() : now()->subDays(30)->startOfDay();
        $endDate = $request->input('end_date') ? now()->parse($request->input('end_date'))->endOfDay() : now()->endOfDay();

        $userRegistrationData = User::where('is_admin', false)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topCustomers = User::where('is_admin', false)
            ->withSum(['orders as total_spent' => function($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')->whereBetween('completed_at', [$startDate, $endDate]);
            }], 'total')
            ->withCount(['orders as total_orders' => function($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')->whereBetween('completed_at', [$startDate, $endDate]);
            }])
            ->orderByDesc('total_spent')
            ->take(10)
            ->get()
            ->filter(fn($user) => $user->total_spent > 0)
            ->values();

        $activeUsersCount = User::where('is_admin', false)->where('is_active', true)->count();
        $inactiveUsersCount = User::where('is_admin', false)->where('is_active', false)->count();

        return view('admin.statistics.users', compact(
            'userRegistrationData',
            'topCustomers',
            'activeUsersCount',
            'inactiveUsersCount',
            'startDate',
            'endDate'
        ));
    }
}
