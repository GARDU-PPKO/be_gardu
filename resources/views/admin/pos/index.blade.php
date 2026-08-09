@extends('admin.layouts.app')

@section('title', 'Terminal Kasir POS')

@section('content')
<div class="h-[calc(100vh-5rem)] flex flex-col md:flex-row gap-5 -m-6 p-5 bg-slate-100 font-sans overflow-hidden" id="posContainer">
    <!-- Left Section: Search Header, Category Pills & Product Catalog -->
    <div class="flex-1 flex flex-col min-w-0 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        
        <!-- Header Bar -->
        <div class="p-4 border-b border-slate-100 bg-slate-900 text-white flex flex-col sm:flex-row gap-3 items-center justify-between">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 flex-shrink-0">
                    <svg width="20" height="20" style="width:20px;height:20px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h2 class="font-bold text-sm sm:text-base leading-tight">Terminal Kasir POS</h2>
                    <p class="text-[11px] text-slate-400">Desa Wisata Getas · Penjualan Langsung</p>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative flex-1 max-w-md w-full">
                <input type="text" id="searchInput" placeholder="Cari nama produk atau SKU (Tekan '/' untuk fokus)..." 
                       class="w-full pl-9 pr-9 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                <svg width="16" height="16" style="width:16px;height:16px;flex-shrink:0;" class="text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <kbd class="hidden sm:inline-block absolute right-3 top-2 px-1.5 py-0.5 text-[10px] font-semibold text-slate-400 bg-slate-700 rounded border border-slate-600">/</kbd>
            </div>

            <!-- Shortcuts -->
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <a href="{{ route('admin.pos.transactions.index') }}" class="px-3 py-1.5 text-xs font-medium text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-xl transition flex items-center gap-1.5 border border-slate-700">
                    <svg width="14" height="14" style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span>Riwayat</span>
                </a>
                <a href="{{ route('admin.pos.products.index') }}" class="px-3 py-1.5 text-xs font-medium text-emerald-300 hover:text-emerald-200 bg-emerald-950/60 hover:bg-emerald-900/80 rounded-xl transition flex items-center gap-1.5 border border-emerald-700/50">
                    <svg width="14" height="14" style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span>Produk</span>
                </a>
            </div>
        </div>

        <!-- Categories Filter Bar -->
        <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50 flex gap-2 overflow-x-auto scrollbar-none items-center">
            <button type="button" class="category-btn px-4 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-200 bg-emerald-600 text-white shadow-sm shadow-emerald-600/30" data-category="all">
                Semua Produk ({{ count($products) }})
            </button>
            @foreach($categories as $cat)
                @php
                    $countInCat = $products->where('category_id', $cat->id)->count();
                @endphp
                <button type="button" class="category-btn px-4 py-1.5 rounded-xl text-xs font-medium whitespace-nowrap transition-all duration-200 bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 hover:text-slate-900" data-category="{{ $cat->id }}">
                    {{ $cat->name }} <span class="text-[10px] opacity-70 ml-1">({{ $countInCat }})</span>
                </button>
            @endforeach
        </div>

        <!-- Product Cards Grid -->
        <div class="flex-1 p-4 overflow-y-auto grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5 items-start align-content-start bg-slate-50/50" id="productGrid">
            @forelse($products as $prod)
                <div class="product-card group bg-white border border-slate-200/90 rounded-2xl p-3 flex flex-col justify-between items-stretch hover:border-emerald-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer relative overflow-hidden active:scale-[0.98] self-start"
                     data-id="{{ $prod->id }}"
                     data-name="{{ $prod->name }}"
                     data-price="{{ $prod->price }}"
                     data-stock="{{ $prod->stock }}"
                     data-category="{{ $prod->category_id }}">
                    
                    <div class="relative w-full h-24 mb-2 rounded-xl bg-slate-100 overflow-hidden flex items-center justify-center border border-slate-100 flex-shrink-0">
                        @if($prod->image)
                            <img src="{{ $prod->image }}" alt="{{ $prod->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-300">
                                <svg width="32" height="32" style="width:32px;height:32px;flex-shrink:0;" class="stroke-1 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            </div>
                        @endif

                        <!-- Stock Badge -->
                        <span class="absolute top-2 right-2 px-2 py-0.5 rounded-full text-[10px] font-bold backdrop-blur-md shadow-xs {{ $prod->stock > 5 ? 'bg-emerald-600 text-white' : ($prod->stock > 0 ? 'bg-amber-500 text-white' : 'bg-rose-500 text-white') }}">
                            {{ $prod->stock > 0 ? 'Stok: '.$prod->stock : 'Habis' }}
                        </span>
                    </div>

                    <div class="flex-1 flex flex-col justify-between gap-1.5">
                        <div>
                            <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400 truncate">
                                {{ $prod->category->name ?? 'Umum' }}
                            </div>
                            <h4 class="font-bold text-slate-800 text-xs sm:text-sm line-clamp-2 leading-snug group-hover:text-emerald-700 transition-colors">
                                {{ $prod->name }}
                            </h4>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 mt-1">
                            <span class="text-emerald-700 font-extrabold text-xs sm:text-sm">
                                Rp {{ number_format($prod->price, 0, ',', '.') }}
                            </span>
                            <button type="button" class="w-7 h-7 rounded-lg bg-emerald-50 group-hover:bg-emerald-600 group-hover:text-white text-emerald-700 flex items-center justify-center transition-colors flex-shrink-0">
                                <svg width="16" height="16" style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center text-slate-400">
                    <svg width="48" height="48" style="width:48px;height:48px;flex-shrink:0;" class="mx-auto mb-2 text-slate-300 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    <p class="text-xs font-semibold">Belum ada produk POS yang aktif.</p>
                    <a href="{{ route('admin.pos.products.index') }}" class="text-xs text-emerald-600 hover:underline font-bold mt-1 inline-block">Tambah Produk Baru &rarr;</a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right Section: Interactive Shopping Cart Sidebar -->
    <div class="w-full md:w-[380px] lg:w-[420px] flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        
        <!-- Cart Header -->
        <div class="p-4 border-b border-slate-200 bg-slate-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg width="20" height="20" style="width:20px;height:20px;flex-shrink:0;" class="text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                <h3 class="font-bold text-sm sm:text-base">Keranjang Pesanan</h3>
            </div>
            <button type="button" id="clearCartBtn" class="px-2.5 py-1 text-[11px] font-semibold text-rose-300 hover:text-white hover:bg-rose-900/40 rounded-lg transition border border-rose-800/40">
                Kosongkan
            </button>
        </div>

        <!-- Cart Items Container -->
        <div class="flex-1 p-4 overflow-y-auto space-y-2.5 bg-slate-50/30" id="cartItemsList">
            <div id="emptyCartMessage" class="h-full flex flex-col items-center justify-center text-center py-16 text-slate-400">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-300 mb-3 border border-slate-200">
                    <svg width="32" height="32" style="width:32px;height:32px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <p class="text-xs font-bold text-slate-600">Keranjang masih kosong</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Pilih produk di katalog untuk menambahkan pesanan</p>
            </div>
        </div>

        <!-- Checkout & Payment Control Panel -->
        <div class="p-4 border-t border-slate-200 bg-white space-y-3 shadow-lg">
            
            <!-- Summary Totals -->
            <div class="space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-500">
                    <span>Jumlah Pesanan:</span>
                    <span id="cartTotalItems" class="font-bold text-slate-800">0 item</span>
                </div>
                <div class="flex justify-between items-baseline pt-2 border-t border-slate-100">
                    <span class="text-slate-800 font-bold text-sm">Total Tagihan:</span>
                    <span id="cartTotalAmount" class="text-emerald-700 font-black text-xl">Rp 0</span>
                </div>
            </div>

            <!-- Payment Method Selector Tabs -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Metode Pembayaran</label>
                <div class="grid grid-cols-3 gap-1.5" id="paymentMethodContainer">
                    <button type="button" class="pay-method-btn px-2 py-2 rounded-xl border border-emerald-600 bg-emerald-50 text-emerald-800 font-bold text-xs flex items-center justify-center gap-1.5 transition-all shadow-xs" data-method="cash">
                        <svg width="16" height="16" style="width:16px;height:16px;flex-shrink:0;" class="text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span>Tunai</span>
                    </button>
                    <button type="button" class="pay-method-btn px-2 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 font-semibold text-xs flex items-center justify-center gap-1.5 hover:bg-slate-50 transition-all" data-method="qris">
                        <svg width="16" height="16" style="width:16px;height:16px;flex-shrink:0;" class="text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <span>QRIS</span>
                    </button>
                    <button type="button" class="pay-method-btn px-2 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 font-semibold text-xs flex items-center justify-center gap-1.5 hover:bg-slate-50 transition-all" data-method="transfer">
                        <svg width="16" height="16" style="width:16px;height:16px;flex-shrink:0;" class="text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                        <span>Transfer</span>
                    </button>
                </div>
            </div>

            <!-- Cash Input Section -->
            <div id="cashPaymentSection" class="space-y-2 pt-1">
                <div class="flex justify-between items-center">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nominal Uang (Rp)</label>
                    <button type="button" id="exactAmountBtn" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800 hover:underline">
                        Uang Pas
                    </button>
                </div>
                
                <input type="text" id="paidAmountInput" placeholder="0" inputmode="numeric" autocomplete="off"
                       class="w-full px-3 py-2 text-right font-black text-lg rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                
                <!-- Quick Nominal Denomination Pills -->
                <div class="grid grid-cols-4 gap-1">
                    <button type="button" class="quick-nominal-btn py-1 rounded-lg bg-slate-100 border border-slate-200 text-[11px] font-bold text-slate-700 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-800 transition" data-amount="10000">10k</button>
                    <button type="button" class="quick-nominal-btn py-1 rounded-lg bg-slate-100 border border-slate-200 text-[11px] font-bold text-slate-700 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-800 transition" data-amount="20000">20k</button>
                    <button type="button" class="quick-nominal-btn py-1 rounded-lg bg-slate-100 border border-slate-200 text-[11px] font-bold text-slate-700 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-800 transition" data-amount="50000">50k</button>
                    <button type="button" class="quick-nominal-btn py-1 rounded-lg bg-slate-100 border border-slate-200 text-[11px] font-bold text-slate-700 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-800 transition" data-amount="100000">100k</button>
                </div>

                <div class="flex justify-between items-center text-xs pt-1.5 border-t border-slate-100">
                    <span class="font-medium text-slate-600">Kembalian:</span>
                    <span id="changeAmountText" class="font-black text-sm text-slate-900">Rp 0</span>
                </div>
            </div>

            <!-- Submit Payment Button -->
            <button type="button" id="checkoutBtn" disabled 
                    class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black rounded-xl shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-2 text-sm tracking-wide active:scale-[0.99]">
                <svg width="20" height="20" style="width:20px;height:20px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>PROSES PEMBAYARAN</span>
            </button>
        </div>
    </div>
</div>

<!-- Premium Receipt Dialog Modal -->
<div id="receiptModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4 animate-in fade-in duration-200">
    <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 text-center border border-slate-100 transform scale-100 transition-all">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3 shadow-inner flex-shrink-0">
            <svg width="32" height="32" style="width:32px;height:32px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <h3 class="text-lg font-bold text-slate-900 mb-0.5">Transaksi Sukses!</h3>
        <p class="text-xs text-slate-500 mb-4 font-mono" id="modalInvoiceText">POS-20260809-XXXX</p>
        
        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 mb-5 text-xs text-left space-y-2">
            <div class="flex justify-between items-center">
                <span class="text-slate-500 font-medium">Uang Kembalian:</span>
                <span class="font-black text-emerald-700 text-base" id="modalChangeText">Rp 0</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2.5">
            <button type="button" id="closeModalBtn" class="py-2.5 px-4 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 transition">
                Tutup
            </button>
            <a href="#" id="printReceiptBtn" target="_blank" class="py-2.5 px-4 rounded-xl bg-emerald-700 text-white font-bold text-xs hover:bg-emerald-800 transition flex items-center justify-center gap-1.5 shadow-sm shadow-emerald-700/20">
                <svg width="16" height="16" style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak Struk</span>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let cart = [];
    let selectedPaymentMethod = 'cash';

    const productCards = document.querySelectorAll('.product-card');
    const categoryBtns = document.querySelectorAll('.category-btn');
    const searchInput = document.getElementById('searchInput');
    const cartItemsList = document.getElementById('cartItemsList');
    const emptyCartMessage = document.getElementById('emptyCartMessage');
    const cartTotalItems = document.getElementById('cartTotalItems');
    const cartTotalAmount = document.getElementById('cartTotalAmount');
    const paidAmountInput = document.getElementById('paidAmountInput');
    const changeAmountText = document.getElementById('changeAmountText');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const exactAmountBtn = document.getElementById('exactAmountBtn');
    const paymentMethodBtns = document.querySelectorAll('.pay-method-btn');
    const cashPaymentSection = document.getElementById('cashPaymentSection');
    const quickNominalBtns = document.querySelectorAll('.quick-nominal-btn');

    // Modal elements
    const receiptModal = document.getElementById('receiptModal');
    const modalInvoiceText = document.getElementById('modalInvoiceText');
    const modalChangeText = document.getElementById('modalChangeText');
    const printReceiptBtn = document.getElementById('printReceiptBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');

    // Helper: Parse integer from formatted text input
    function getRawPaidAmount() {
        const clean = paidAmountInput.value.replace(/[^0-9]/g, '');
        return clean ? parseInt(clean, 10) : 0;
    }

    // Helper: Format input value as Indonesian Rupiah with dots
    function updatePaidAmountDisplay(valNum) {
        if (!valNum || valNum <= 0) {
            paidAmountInput.value = '';
        } else {
            paidAmountInput.value = formatRupiah(valNum);
        }
        calculateChange();
    }

    // Keyboard shortcut '/' to search
    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && document.activeElement !== searchInput) {
            e.preventDefault();
            searchInput.focus();
        }
    });

    // Category Filter
    categoryBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            categoryBtns.forEach(b => {
                b.classList.remove('bg-emerald-600', 'text-white', 'shadow-sm', 'shadow-emerald-600/30');
                b.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200');
            });
            btn.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200');
            btn.classList.add('bg-emerald-600', 'text-white', 'shadow-sm', 'shadow-emerald-600/30');

            const category = btn.dataset.category;
            filterProducts(category, searchInput.value.toLowerCase());
        });
    });

    // Search Input
    searchInput.addEventListener('input', (e) => {
        const activeCategory = document.querySelector('.category-btn.bg-emerald-600')?.dataset.category || 'all';
        filterProducts(activeCategory, e.target.value.toLowerCase());
    });

    function filterProducts(category, search) {
        productCards.forEach(card => {
            const matchesCategory = category === 'all' || card.dataset.category === category;
            const matchesSearch = card.dataset.name.toLowerCase().includes(search);
            if (matchesCategory && matchesSearch) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    // Add Product to Cart
    productCards.forEach(card => {
        card.addEventListener('click', () => {
            const id = parseInt(card.dataset.id);
            const name = card.dataset.name;
            const price = parseFloat(card.dataset.price);
            const stock = parseInt(card.dataset.stock);

            if (stock <= 0) {
                alert('Stok produk habis!');
                return;
            }

            const existingIndex = cart.findIndex(item => item.id === id);
            if (existingIndex > -1) {
                if (cart[existingIndex].quantity + 1 > stock) {
                    alert('Jumlah melebihi stok yang tersedia (' + stock + ')');
                    return;
                }
                cart[existingIndex].quantity++;
            } else {
                cart.push({ id, name, price, stock, quantity: 1 });
            }

            renderCart();
        });
    });

    // Render Cart Items
    function renderCart() {
        if (cart.length === 0) {
            emptyCartMessage.classList.remove('hidden');
            cartItemsList.innerHTML = '';
            cartItemsList.appendChild(emptyCartMessage);
            cartTotalItems.textContent = '0 item';
            cartTotalAmount.textContent = 'Rp 0';
            checkoutBtn.disabled = true;
            paidAmountInput.value = '';
            changeAmountText.textContent = 'Rp 0';
            return;
        }

        emptyCartMessage.classList.add('hidden');
        cartItemsList.innerHTML = '';

        let totalQty = 0;
        let totalPrice = 0;

        cart.forEach((item, index) => {
            totalQty += item.quantity;
            const subtotal = item.price * item.quantity;
            totalPrice += subtotal;

            const itemEl = document.createElement('div');
            itemEl.className = 'flex items-center justify-between p-3 rounded-2xl border border-slate-200/80 bg-white shadow-2xs text-xs hover:border-emerald-300 transition-colors';
            itemEl.innerHTML = `
                <div class="flex-1 min-w-0 pr-2">
                    <h5 class="font-bold text-slate-800 truncate text-xs">${item.name}</h5>
                    <div class="text-slate-500 text-[11px] mt-0.5">
                        Rp ${formatRupiah(item.price)} x ${item.quantity} = <strong class="text-emerald-700 font-extrabold">Rp ${formatRupiah(subtotal)}</strong>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <button type="button" class="decrease-btn w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center transition active:scale-95" data-index="${index}">-</button>
                    <span class="font-extrabold text-slate-900 w-5 text-center text-xs">${item.quantity}</span>
                    <button type="button" class="increase-btn w-7 h-7 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold flex items-center justify-center transition active:scale-95" data-index="${index}">+</button>
                    <button type="button" class="remove-btn w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold flex items-center justify-center ml-1 transition active:scale-95" data-index="${index}">✕</button>
                </div>
            `;
            cartItemsList.appendChild(itemEl);
        });

        cartTotalItems.textContent = totalQty + ' item';
        cartTotalAmount.textContent = 'Rp ' + formatRupiah(totalPrice);
        checkoutBtn.disabled = false;

        calculateChange();
    }

    // Quantity Counter Handlers
    cartItemsList.addEventListener('click', (e) => {
        if (e.target.classList.contains('increase-btn')) {
            const index = parseInt(e.target.dataset.index);
            if (cart[index].quantity + 1 > cart[index].stock) {
                alert('Stok produk tidak mencukupi');
                return;
            }
            cart[index].quantity++;
            renderCart();
        } else if (e.target.classList.contains('decrease-btn')) {
            const index = parseInt(e.target.dataset.index);
            if (cart[index].quantity > 1) {
                cart[index].quantity--;
            } else {
                cart.splice(index, 1);
            }
            renderCart();
        } else if (e.target.classList.contains('remove-btn')) {
            const index = parseInt(e.target.dataset.index);
            cart.splice(index, 1);
            renderCart();
        }
    });

    clearCartBtn.addEventListener('click', () => {
        if (cart.length > 0 && confirm('Kosongkan semua item dari keranjang?')) {
            cart = [];
            renderCart();
        }
    });

    // Payment Method Selection
    paymentMethodBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            paymentMethodBtns.forEach(b => {
                b.classList.remove('border-emerald-600', 'bg-emerald-50', 'text-emerald-800', 'shadow-xs');
                b.classList.add('border-slate-200', 'bg-white', 'text-slate-600');
            });
            btn.classList.remove('border-slate-200', 'bg-white', 'text-slate-600');
            btn.classList.add('border-emerald-600', 'bg-emerald-50', 'text-emerald-800', 'shadow-xs');

            selectedPaymentMethod = btn.dataset.method;

            if (selectedPaymentMethod === 'cash') {
                cashPaymentSection.classList.remove('hidden');
            } else {
                cashPaymentSection.classList.add('hidden');
            }
            calculateChange();
        });
    });

    function getTotalPrice() {
        return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    }

    function calculateChange() {
        if (selectedPaymentMethod !== 'cash') {
            changeAmountText.textContent = 'Rp 0';
            return;
        }

        const total = getTotalPrice();
        const paid = getRawPaidAmount();
        const change = paid - total;

        if (change >= 0) {
            changeAmountText.textContent = 'Rp ' + formatRupiah(change);
            changeAmountText.className = 'font-black text-sm text-emerald-700';
        } else {
            changeAmountText.textContent = 'Kurang Rp ' + formatRupiah(Math.abs(change));
            changeAmountText.className = 'font-black text-sm text-rose-600';
        }
    }

    // Real-time dot-formatting on cash input while typing
    paidAmountInput.addEventListener('input', () => {
        const raw = getRawPaidAmount();
        if (raw <= 0) {
            paidAmountInput.value = '';
        } else {
            paidAmountInput.value = formatRupiah(raw);
        }
        calculateChange();
    });

    exactAmountBtn.addEventListener('click', () => {
        updatePaidAmountDisplay(getTotalPrice());
    });

    quickNominalBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const amount = parseInt(btn.dataset.amount, 10);
            updatePaidAmountDisplay(amount);
        });
    });

    // Submit Checkout
    checkoutBtn.addEventListener('click', async () => {
        if (cart.length === 0) return;

        const total = getTotalPrice();
        const paid = getRawPaidAmount();

        if (selectedPaymentMethod === 'cash' && paid < total) {
            alert('Jumlah uang pembayaran kurang dari total tagihan!');
            return;
        }

        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = `
            <svg width="20" height="20" style="width:20px;height:20px;flex-shrink:0;" class="animate-spin text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>MEMPROSES...</span>
        `;

        const payload = {
            items: cart.map(item => ({ product_id: item.id, quantity: item.quantity })),
            paid_amount: selectedPaymentMethod === 'cash' ? paid : total,
            payment_method: selectedPaymentMethod,
            _token: '{{ csrf_token() }}'
        };

        try {
            const response = await fetch('{{ route('admin.pos.checkout') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                modalInvoiceText.textContent = data.invoice_number;
                modalChangeText.textContent = 'Rp ' + formatRupiah(data.change_amount);
                printReceiptBtn.href = data.redirect_url;
                receiptModal.classList.remove('hidden');

                cart = [];
                renderCart();
            } else {
                alert(data.message || 'Terjadi kesalahan saat memproses transaksi');
            }
        } catch (err) {
            console.error(err);
            alert('Gagal menghubungi server. Silakan coba lagi.');
        } finally {
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = `
                <svg width="20" height="20" style="width:20px;height:20px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>PROSES PEMBAYARAN</span>
            `;
        }
    });

    closeModalBtn.addEventListener('click', () => {
        receiptModal.classList.add('hidden');
    });

    function formatRupiah(amount) {
        return new Intl.NumberFormat('id-ID').format(amount);
    }
});
</script>
@endpush
