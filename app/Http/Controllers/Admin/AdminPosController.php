<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\BookingSession;
use App\Models\PaketWisata;
use App\Models\PosCategory;
use App\Models\PosProduct;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\UmkmProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminPosController extends Controller
{
    /**
     * Display POS Terminal — katalog gabungan (paket wisata, produk UMKM, produk POS, add-on).
     */
    public function index(Request $request)
    {
        $posProducts = PosProduct::with('category')->where('is_active', true)
            ->orderBy('name', 'asc')->get();

        $umkmProducts = UmkmProduct::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('stock')->orWhere('stock', '>', 0);
            })
            ->orderBy('nama', 'asc')->get();

        $paketWisata = PaketWisata::with('tiers')->where('aktif', true)
            ->orderBy('nama')->get();

        $addOns = AddOn::where('aktif', true)
            ->orderBy('urutan')->orderBy('nama')->get();

        $catalog = collect();

        foreach ($posProducts as $p) {
            $catalog->push([
                'type' => 'pos_product',
                'id' => (string) $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'stock' => $p->stock,
                'image' => $p->image,
                'category' => $p->category->name ?? 'Umum',
                'sku' => $p->sku,
                'sub_label' => 'Produk POS',
            ]);
        }

        foreach ($umkmProducts as $u) {
            $catalog->push([
                'type' => 'umkm_product',
                'id' => (string) $u->id,
                'name' => $u->nama,
                'price' => (float) $u->harga,
                'stock' => $u->stock ?? 99,
                'image' => $u->gambar,
                'category' => $u->kategori,
                'sku' => $u->sku,
                'sub_label' => 'Produk UMKM',
            ]);
        }

        foreach ($paketWisata as $pk) {
            $isPerOrang = $pk->tipe_harga === 'per_orang_tier';
            $tiers = $pk->tiers->sortBy('min_peserta')->values();

            $catalog->push([
                'type' => 'paket_wisata',
                'id' => (string) $pk->id,
                'name' => $pk->nama,
                'price' => $isPerOrang
                    ? (float) ($tiers->first()->harga_per_orang ?? 0)
                    : (float) ($pk->harga_paket ?? 0),
                'stock' => null,
                'image' => $pk->gambar,
                'category' => $pk->kategori,
                'sku' => null,
                'sub_label' => 'Paket Wisata',
                'is_per_orang' => $isPerOrang,
                'min_participants' => $isPerOrang ? (int) ($tiers->first()->min_peserta ?? 1) : 1,
            ]);
        }

        foreach ($addOns as $ad) {
            $catalog->push([
                'type' => 'addon',
                'id' => (string) $ad->id,
                'name' => $ad->nama,
                'price' => (float) $ad->harga,
                'stock' => null,
                'image' => $ad->gambar,
                'category' => $ad->kategori ?? 'Add-On',
                'sku' => null,
                'sub_label' => 'Add-On (' . ($ad->tipe_harga === 'per_orang' ? 'Per Orang' : 'Per Unit') . ')',
                'is_per_orang' => $ad->tipe_harga === 'per_orang',
                'min_participants' => 1,
            ]);
        }

        $categories = PosCategory::orderBy('name', 'asc')->get();
        $sessions = BookingSession::where('is_active', true)->orderBy('id')->get();

        return view('admin.pos.index', compact('catalog', 'categories', 'sessions'));
    }

    /**
     * Store POS Transaction (Checkout) — mendukung item polymorphic.
     */
    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:pos_product,umkm_product,paket_wisata,addon',
            'items.*.item_id' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qris,transfer',
            'notes' => 'nullable|string|max:500',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'visit_date' => 'nullable|date_format:Y-m-d',
            'sesi' => 'nullable|string|max:50',
        ]);


        $hasPaket = collect($validated['items'])->contains('item_type', 'paket_wisata');

        if ($hasPaket) {
            $visitor = $request->validate([
                'customer_name' => 'required|string|max:100',
                'customer_phone' => 'required|string|max:20',
                'visit_date' => 'required|date_format:Y-m-d',
                'sesi' => 'required|string|max:50',
            ]);
        }

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $itemsToCreate = [];

            foreach ($validated['items'] as $itemData) {
                $quantity = $itemData['quantity'];

                switch ($itemData['item_type']) {
                    case 'pos_product':
                        $product = PosProduct::lockForUpdate()->find($itemData['item_id']);
                        if (! $product) {
                            throw new \Exception("Produk POS tidak ditemukan.");
                        }
                        if ($product->stock < $quantity) {
                            throw new \Exception("Stok produk {$product->name} tidak mencukupi (Stok: {$product->stock}).");
                        }
                        $subtotal = (float) $product->price * $quantity;
                        $totalAmount += $subtotal;
                        $itemsToCreate[] = [
                            'item_type' => 'pos_product',
                            'item_id' => $product->id,
                            'model' => $product,
                            'product_name' => $product->name,
                            'price' => (float) $product->price,
                            'quantity' => $quantity,
                            'subtotal' => $subtotal,
                            'booking' => null,
                        ];
                        break;

                    case 'umkm_product':
                        $product = UmkmProduct::lockForUpdate()->find($itemData['item_id']);
                        if (! $product) {
                            throw new \Exception("Produk UMKM tidak ditemukan.");
                        }
                        if ($product->stock < $quantity) {
                            throw new \Exception("Stok produk {$product->nama} tidak mencukupi (Stok: {$product->stock}).");
                        }
                        $subtotal = (float) $product->harga * $quantity;
                        $totalAmount += $subtotal;
                        $itemsToCreate[] = [
                            'item_type' => 'umkm_product',
                            'item_id' => $product->id,
                            'model' => $product,
                            'product_name' => $product->nama,
                            'price' => (float) $product->harga,
                            'quantity' => $quantity,
                            'subtotal' => $subtotal,
                            'booking' => null,
                        ];
                        break;

                    case 'paket_wisata':
                        $paket = PaketWisata::where('aktif', true)->find($itemData['item_id']);
                        if (! $paket) {
                            throw new \Exception("Paket wisata tidak ditemukan.");
                        }
                        $session = BookingSession::where('sesi', $visitor['sesi'])
                            ->where('is_active', true)
                            ->first();
                        if (! $session) {
                            throw new \Exception("Sesi \"{$visitor['sesi']}\" tidak tersedia.");
                        }
                        $sisaKuota = $session->sisaPadaTanggal($visitor['visit_date']);
                        if ($sisaKuota !== null && $quantity > $sisaKuota) {
                            throw new \Exception("Kuota sesi tidak mencukupi untuk {$quantity} peserta (sisa {$sisaKuota}).");
                        }

                        try {
                            $hitung = $paket->hitungTotalHarga($quantity);
                        } catch (\Exception $e) {
                            throw new \Exception($e->getMessage());
                        }
                        $subtotal = (float) $hitung['total'];
                        $totalAmount += $subtotal;
                        $itemsToCreate[] = [
                            'item_type' => 'paket_wisata',
                            'item_id' => $paket->id,
                            'model' => $paket,
                            'product_name' => $paket->nama,
                            'price' => (float) round($subtotal / $quantity, 2),
                            'quantity' => $quantity,
                            'subtotal' => $subtotal,
                            'booking' => null,
                        ];
                        break;

                    case 'addon':
                        $addOn = AddOn::where('aktif', true)->find($itemData['item_id']);
                        if (! $addOn) {
                            throw new \Exception("Add-On tidak ditemukan.");
                        }
                        $subtotal = (float) $addOn->harga * $quantity;
                        $totalAmount += $subtotal;
                        $itemsToCreate[] = [
                            'item_type' => 'addon',
                            'item_id' => (string) $addOn->id,
                            'model' => $addOn,
                            'product_name' => $addOn->nama,
                            'price' => (float) $addOn->harga,
                            'quantity' => $quantity,
                            'subtotal' => $subtotal,
                            'booking' => null,
                        ];
                        break;
                }
            }


            if ($validated['payment_method'] === 'cash' && $validated['paid_amount'] < $totalAmount) {
                throw new \Exception("Jumlah uang dibayar (Rp " . number_format($validated['paid_amount'], 0, ',', '.') . ") kurang dari total belanja (Rp " . number_format($totalAmount, 0, ',', '.') . ").");
            }

            $paidAmount = $validated['payment_method'] !== 'cash' ? $totalAmount : $validated['paid_amount'];
            $changeAmount = max(0, $paidAmount - $totalAmount);

            $invoiceNumber = 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $transaction = PosTransaction::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => auth()->id(),
                'customer_name' => $validated['customer_name'] ?? null,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'completed',
            ]);

            foreach ($itemsToCreate as $item) {
                $itemPayload = [
                    'transaction_id' => $transaction->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'product_name' => $item['product_name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ];

                if ($item['item_type'] === 'paket_wisata') {
                    $booking = Booking::create([
                        'booking_code' => Booking::generateBookingCode(),
                        'nama_lengkap' => $visitor['customer_name'],
                        'no_whatsapp' => $visitor['customer_phone'],
                        'email' => null,
                        'alamat' => null,
                        'kontak_darurat_nama' => null,
                        'kontak_darurat_telp' => null,
                        'notes' => 'Booking walk-in via POS ' . $invoiceNumber,
                        'jumlah_peserta' => $item['quantity'],
                        'tanggal_kunjungan' => $visitor['visit_date'],
                        'paket_wisata_id' => $item['item_id'],
                        'sesi' => $visitor['sesi'],
                        'total_harga' => $item['subtotal'],
                        'status' => Booking::STATUS_CONFIRMED,
                        'metode_pembayaran' => $validated['payment_method'],
                        'verified_by' => auth()->id(),
                        'verified_at' => now(),
                        'created_by' => auth()->id(),
                    ]);

                    BookingLog::create([
                        'booking_id' => $booking->id,
                        'admin_id' => auth()->id(),
                        'action' => 'confirmed',
                        'detail' => 'Booking walk-in dibuat via POS ' . $invoiceNumber,
                        'created_at' => now(),
                    ]);

                    $itemPayload['booking_id'] = $booking->id;
                }

                PosTransactionItem::create($itemPayload);

                if (in_array($item['item_type'], ['pos_product', 'umkm_product'], true)) {
                    $item['model']->decrement('stock', $item['quantity']);
                }
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
     * Batalkan transaksi POS — kembalikan stok & batalkan booking terkait.
     */
    public function cancelTransaction($id)
    {
        $transaction = PosTransaction::with('items')->findOrFail($id);

        if ($transaction->status === 'cancelled') {
            return redirect()->route('admin.pos.transactions.index')->with('error', 'Transaksi sudah berstatus dibatalkan.');
        }

        try {
            DB::beginTransaction();

            $transaction->update(['status' => 'cancelled']);

            foreach ($transaction->items as $item) {
                if (in_array($item->item_type, ['pos_product', 'umkm_product'], true)) {
                    $item->item?->increment('stock', $item->quantity);
                }

                if ($item->item_type === 'paket_wisata' && $item->booking_id) {
                    $booking = Booking::find($item->booking_id);
                    if ($booking) {
                        $booking->update(['status' => Booking::STATUS_CANCELLED]);
                        BookingLog::create([
                            'booking_id' => $booking->id,
                            'admin_id' => auth()->id(),
                            'action' => 'cancelled_via_pos',
                            'detail' => 'Booking dibatalkan karena transaksi POS ' . $transaction->invoice_number . ' dibatalkan.',
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.pos.transactions.index')->with('success', 'Transaksi berhasil dibatalkan. Stok dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.pos.transactions.index')->with('error', $e->getMessage());
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
