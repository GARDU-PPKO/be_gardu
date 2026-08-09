<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosCategory;
use App\Models\PosProduct;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminPosController extends Controller
{
    /**
     * Display POS Terminal
     */
    public function index(Request $request)
    {
        $query = PosProduct::with('category')->where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name', 'asc')->get();
        $categories = PosCategory::orderBy('name', 'asc')->get();

        return view('admin.pos.index', compact('products', 'categories'));
    }

    /**
     * Store POS Transaction (Checkout)
     */
    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:pos_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qris,transfer',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Calculate totals and verify stocks
            $totalAmount = 0;
            $itemsToCreate = [];

            foreach ($validated['items'] as $itemData) {
                $product = PosProduct::lockForUpdate()->find($itemData['product_id']);

                if (!$product) {
                    throw new \Exception("Produk dengan ID {$itemData['product_id']} tidak ditemukan.");
                }

                if ($product->stock < $itemData['quantity']) {
                    throw new \Exception("Stok produk {$product->name} tidak mencukupi (Stok: {$product->stock}).");
                }

                $price = $product->price;
                $quantity = $itemData['quantity'];
                $subtotal = $price * $quantity;
                $totalAmount += $subtotal;

                $itemsToCreate[] = [
                    'product' => $product,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $price,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ];
            }

            if ($validated['payment_method'] === 'cash' && $validated['paid_amount'] < $totalAmount) {
                throw new \Exception("Jumlah uang dibayar (Rp " . number_format($validated['paid_amount'], 0, ',', '.') . ") kurang dari total belanja (Rp " . number_format($totalAmount, 0, ',', '.') . ").");
            }

            $paidAmount = $validated['payment_method'] !== 'cash' ? $totalAmount : $validated['paid_amount'];
            $changeAmount = max(0, $paidAmount - $totalAmount);

            // Generate Invoice Number
            $invoiceNumber = 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $transaction = PosTransaction::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'completed',
            ]);

            foreach ($itemsToCreate as $item) {
                PosTransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Deduct stock
                $item['product']->decrement('stock', $item['quantity']);
            }

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil!',
                    'invoice_number' => $transaction->invoice_number,
                    'transaction_id' => $transaction->id,
                    'change_amount' => $changeAmount,
                    'redirect_url' => route('admin.pos.receipt', $transaction->id),
                ]);
            }

            return redirect()->route('admin.pos.receipt', $transaction->id)
                             ->with('success', 'Transaksi berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * List POS Products Management
     */
    public function products(Request $request)
    {
        $query = PosProduct::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderBy('name', 'asc')->paginate(15);
        $categories = PosCategory::orderBy('name', 'asc')->get();

        return view('admin.pos.products', compact('products', 'categories'));
    }

    /**
     * Store POS Product
     */
    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:pos_categories,id',
            'sku' => 'nullable|string|max:50|unique:pos_products,sku',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        if (empty($validated['sku'])) {
            $validated['sku'] = 'SKU-' . strtoupper(Str::random(6));
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('pos-products', 'public');
            $validated['image'] = Storage::url($path);
        }

        $validated['is_active'] = $request->has('is_active');

        PosProduct::create($validated);

        return redirect()->route('admin.pos.products.index')->with('success', 'Produk POS berhasil ditambahkan!');
    }

    /**
     * Update POS Product
     */
    public function updateProduct(Request $request, $id)
    {
        $product = PosProduct::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:pos_categories,id',
            'sku' => 'nullable|string|max:50|unique:pos_products,sku,' . $id,
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('pos-products', 'public');
            $validated['image'] = Storage::url($path);
        }

        $validated['is_active'] = $request->has('is_active');

        $product->update($validated);

        return redirect()->route('admin.pos.products.index')->with('success', 'Produk POS berhasil diperbarui!');
    }

    /**
     * Delete POS Product
     */
    public function destroyProduct($id)
    {
        $product = PosProduct::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.pos.products.index')->with('success', 'Produk POS berhasil dihapus!');
    }

    /**
     * Transaction History & Daily Reports
     */
    public function transactions(Request $request)
    {
        $query = PosTransaction::with(['items', 'user']);

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', "%{$request->search}%");
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Daily Summary Stats
        $today = date('Y-m-d');
        $todayRevenue = PosTransaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total_amount');
            
        $todayTransactionsCount = PosTransaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->count();

        return view('admin.pos.transactions', compact('transactions', 'todayRevenue', 'todayTransactionsCount'));
    }

    /**
     * Print Printable Thermal Receipt
     */
    public function receipt($id)
    {
        $transaction = PosTransaction::with(['items', 'user'])->findOrFail($id);

        return view('admin.pos.receipt', compact('transaction'));
    }
}
