<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PreOrder;
use App\Models\Menu;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

abstract class BaseDashboardController extends Controller
{
    protected $role;
    protected $viewPath;

    public function __construct($role, $viewPath)
    {
        $this->middleware('auth');
        $this->middleware("role:$role");
        $this->role = $role;
        $this->viewPath = $viewPath;
    }

    public function dashboard()
    {
        $data = $this->getDashboardData();
        return view("$this->viewPath.dashboard", $data);
    }

    protected function getDashboardData()
    {
        return [
            'totalUsers' => User::count(),
            'totalOrders' => PreOrder::count(),
            'totalMenuItems' => Menu::count(),
            'recentOrders' => PreOrder::with('user')->latest()->take(10)->get()
        ];
    }

    public function reports()
    {
        return view("$this->viewPath.reports");
    }

    public function notifications()
    {
        return view("$this->viewPath.notifications");
    }

    protected function getAnalyticsData()
    {
        // Get monthly revenue for the last 6 months
        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        // Initialize array with all months
        $allMonths = collect();
        for ($date = $startDate->copy(); $date <= $endDate; $date->addMonth()) {
            $allMonths->push([
                'month' => $date->format('M'),
                'revenue' => 0
            ]);
        }

        // Get actual revenue data
        $monthlyRevenue = Order::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::createFromDate($item->year, $item->month, 1)->format('M'),
                    'revenue' => $item->revenue
                ];
            });

        // Merge actual data with all months
        $monthlyRevenue = $allMonths->map(function ($month) use ($monthlyRevenue) {
            $actualData = $monthlyRevenue->firstWhere('month', $month['month']);
            return $actualData ?: $month;
        });

        // Get pre-order statistics (replacing old order system)
        $orderStats = PreOrder::select('is_prepared', DB::raw('count(*) as count'))
            ->groupBy('is_prepared')
            ->get()
            ->map(function($item) {
                return [
                    'status' => $item->is_prepared ? 'completed' : 'pending',
                    'count' => $item->count
                ];
            });

        // Get popular menu items from pre-orders
        $popularMenuItems = PreOrder::select('menus.name', DB::raw('COUNT(*) as order_count'))
            ->join('menus', 'pre_orders.menu_id', '=', 'menus.id')
            ->groupBy('menus.id', 'menus.name')
            ->orderBy('order_count', 'desc')
            ->limit(5)
            ->get();

        // Get daily user activity for the last week
        $userActivity = Order::where('created_at', '>=', Carbon::now()->subDays(7))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(DISTINCT student_id) as active_users')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'day' => Carbon::parse($item->date)->format('D'),
                    'active_users' => $item->active_users
                ];
            });

        return [
            'monthlyRevenue' => $monthlyRevenue,
            'orderStats' => $orderStats,
            'popularMenuItems' => $popularMenuItems,
            'userActivity' => $userActivity
        ];
    }
}
