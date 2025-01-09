<?php

namespace App\Http\Controllers;

use App\Models\BillingSystem;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $query = BillingSystem::with('billingItems.product')
            ->orderBy('created_at', 'desc');

        // Apply date filter if search_date is provided
        if ($request->has('search_date')) {
            $query->whereDate('bill_date', $request->search_date);
        }

        $billings = $query->get();

        return view('billing.index', compact('billings'));
    }

    public function create()
    {
        $products = Product::with('productComponents.inventory')->get();
        
        // Debug information
        foreach ($products as $product) {
            \Log::info("Product components for {$product->product_name}:", [
                'components' => $product->productComponents->map(function($component) {
                    return [
                        'inventory_name' => $component->inventory->inventory_name,
                        'quantity_required' => $component->quantity_required
                    ];
                })
            ]);
        }
        
        return view('billing.create', compact('products'));
    }

    public function store(Request $request)
    {
        \Log::info('Starting billing creation', ['request' => $request->all()]);

        try {
            \DB::beginTransaction();

            // Validate request
            $validated = $request->validate([
                'bill_date' => 'required|date',
                'bill_time' => 'required',
                'items' => 'required|array',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1|max:20',
                'items.*.extra_price' => 'nullable|numeric|min:0',
                'items.*.description' => 'nullable|string|max:255',
            ]);

            // Create billing record
            $billing = BillingSystem::create([
                'bill_date' => $validated['bill_date'],
                'bill_time' => $validated['bill_time'],
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            // Process each item
            foreach ($validated['items'] as $item) {
                $product = Product::with('productComponents.inventory')->find($item['product_id']);
                
                // Check inventory availability
                foreach ($product->productComponents as $component) {
                    $requiredQuantity = $component->quantity_required * $item['quantity'];
                    if ($component->inventory->inventory_quantity < $requiredQuantity) {
                        throw new \Exception("Insufficient inventory for {$component->inventory->inventory_name}");
                    }
                }

                // Calculate total price
                $total_price = ($item['quantity'] * $product->price) + ($item['extra_price'] ?? 0);
                
                // Create billing item
                $billing->billingItems()->create([
                    'product_id' => $item['product_id'],
                    'order_quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'extra_price' => $item['extra_price'] ?? 0,
                    'description' => $item['description'] ?? null,
                    'total_price' => $total_price,
                ]);

                $totalAmount += $total_price;

                // Update inventory quantities
                foreach ($product->productComponents as $component) {
                    $quantityToDecrease = $component->quantity_required * $item['quantity'];
                    \Log::info('Decreasing inventory', [
                        'inventory' => $component->inventory->inventory_name,
                        'decrease_by' => $quantityToDecrease
                    ]);
                    $component->inventory->decreaseQuantity($quantityToDecrease);
                }
            }

            // Update total amount
            $billing->update(['total_amount' => $totalAmount]);

            \DB::commit();

            return redirect()->route('billing.index')
                ->with('success', 'Bill created successfully');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error in billing creation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with('error', 'Error creating bill: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function todayBills()
    {
        $todayBills = BillingSystem::with(['billingItems.product'])
            ->whereDate('bill_date', Carbon::today())
            ->orderBy('bill_time', 'desc')
            ->get();

        $totalRevenue = $todayBills->sum('total_amount');
        $totalBills = $todayBills->count();

        return view('billing.today', compact('todayBills', 'totalRevenue', 'totalBills'));
    }

    public function dashboard()
    {
        // Get date range for the past week
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Get daily sales for the past week
        $dailySales = BillingSystem::select(
            DB::raw('DATE(bill_date) as date'),
            DB::raw('SUM(total_amount) as total_sales'),
            DB::raw('COUNT(*) as bill_count')
        )
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->groupBy('bill_date')
            ->orderBy('bill_date')
            ->get();

        // Get top selling products for the week
        $topProducts = DB::table('billing_items')
            ->join('billing_systems', 'billing_items.bill_id', '=', 'billing_systems.id')
            ->join('products', 'billing_items.product_id', '=', 'products.id')
            ->select(
                'products.product_name',
                DB::raw('SUM(billing_items.order_quantity) as total_quantity'),
                DB::raw('SUM(billing_items.total_price) as total_revenue')
            )
            ->whereBetween('billing_systems.bill_date', [$startDate, $endDate])
            ->groupBy('products.id', 'products.product_name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        // Format data for the chart
        $dates = $dailySales->pluck('date');
        $sales = $dailySales->pluck('total_sales');
        $billCounts = $dailySales->pluck('bill_count');

        // Add logging to debug
        \Log::info('Dashboard Data:', [
            'dates' => $dates,
            'sales' => $sales,
            'billCounts' => $billCounts,
            'topProducts' => $topProducts
        ]);

        return view('dashboard', compact(
            'topProducts',
            'dates',
            'sales',
            'billCounts',
            'dailySales'
        ));
    }
} 