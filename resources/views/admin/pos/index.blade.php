<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS Terminal - Desa Wisata Getas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css'])
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .pos-grid {
            grid-auto-rows: max-content !important;
        }
        #posContainer {
            height: 100vh;
            height: 100svh;
            height: 100dvh;
            padding-bottom: env(safe-area-inset-bottom, 0);
            padding-left: env(safe-area-inset-left, 0);
            padding-right: env(safe-area-inset-right, 0);
        }
    </style>
</head>
<body class="h-full font-sans antialiased bg-slate-100 text-slate-800 select-none overflow-hidden">

<div class="h-screen flex flex-col p-2 sm:p-3 md:p-4 gap-2 md:gap-3 bg-slate-100" id="posContainer">
    <a href="{{ route('admin.dashboard') }}" title="Kembali ke Dashboard"
   class="fixed top-3 left-3 z-40 w-9 h-9 rounded-full bg-slate-900/90 hover:bg-slate-700 text-white flex items-center justify-center shadow-md border border-slate-700 transition active:scale-95">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
    </svg>
</a>
    <!-- Top Bar Navigation (Clean Header) -->
    <!-- <header class="bg-slate-900 text-white rounded-xl px-3 sm:px-4 py-2 sm:py-2.5 shadow-sm flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 flex-shrink-0">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            </div>
            <div class="flex items-center gap-2">
                <h1 class="font-bold text-xs sm:text-sm md:text-base leading-none text-white tracking-tight">
                    Terminal POS
                </h1>
                <span class="text-[9px] sm:text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 whitespace-nowrap hidden xs:inline-block">TABLET</span>
            </div>
        </div>

        <div class="flex items-center gap-1.5 sm:gap-2">
            <span class="hidden lg:inline-block text-xs text-slate-300 mr-1">
                Kasir: <strong class="text-white">{{ auth()->user()->nama ?? auth()->user()->name ?? 'Super Admin' }}</strong>
            </span>

            <a href="{{ route('admin.pos.transactions.index') }}" class="px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition border border-slate-700 flex items-center gap-1">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span class="hidden sm:inline">Riwayat</span>
            </a>

            <a href="{{ route('admin.pos.products.index') }}" class="px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition border border-slate-700 flex items-center gap-1">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span class="hidden sm:inline">Produk</span>
            </a>

            <a href="{{ route('admin.dashboard') }}" class="px-2.5 py-1.5 text-xs font-medium text-rose-300 hover:text-white bg-rose-950/60 hover:bg-rose-900 rounded-lg transition border border-rose-800/50 flex items-center gap-1 shadow-xs">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span class="hidden sm:inline">Kembali ke Admin</span>
                <span class="sm:hidden">Keluar</span>
            </a>
        </div>
    </header> -->

    <!-- Main Tablet Workspace: Side-by-Side 2 Columns (Flex Row) -->
    <div class="flex-1 flex flex-col lg:flex-row gap-2.5 md:gap-3 min-h-0 overflow-hidden">
        
        <!-- Left Section: Catalog (Category Pills Wrap Cleanly, Search Bar, Product Grid) -->
        <div class="flex-1 flex flex-col bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden min-w-0 min-h-0">
            
            <!-- Category Filter & Search Header (Clean Flex Wrap Layout) -->
            <div class="p-2 sm:p-2.5 border-b border-slate-200 bg-white flex flex-col gap-2 flex-shrink-0">
                
                <!-- Row 1: Search Input (Full Width on catalog header) -->
                <div class="relative w-full">
                    <input type="text" id="searchInput" placeholder="Cari nama produk atau SKU..." 
                           class="w-full pl-8 pr-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 text-xs focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition">
                    <svg width="14" height="14" class="text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <!-- Row 2: Category Filter Pills (Flex Wrap so ALL pills wrap and 100% visible without clipping) -->
                <div class="flex flex-wrap gap-1.5 w-full py-0.5">
                    <button type="button" class="type-btn px-2.5 py-1 rounded-lg text-[11px] sm:text-xs font-semibold transition bg-slate-900 text-white shadow-xs" data-type="all">
                        Semua ({{ $catalog->count() }})
                    </button>
                    <button type="button" class="type-btn px-2.5 py-1 rounded-lg text-[11px] sm:text-xs font-medium transition bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200" data-type="paket_wisata">
                        Paket Wisata ({{ $catalog->where('type', 'paket_wisata')->count() }})
                    </button>
                    <button type="button" class="type-btn px-2.5 py-1 rounded-lg text-[11px] sm:text-xs font-medium transition bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200" data-type="umkm_product">
                        Produk UMKM ({{ $catalog->where('type', 'umkm_product')->count() }})
                    </button>
                    <button type="button" class="type-btn px-2.5 py-1 rounded-lg text-[11px] sm:text-xs font-medium transition bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200" data-type="pos_product">
                        Produk POS ({{ $catalog->where('type', 'pos_product')->count() }})
                    </button>
                </div>
            </div>

            <!-- Product Cards Catalog Grid (Entire Card Clickable to Add directly to Cart) -->
            <div class="flex-1 p-2.5 sm:p-3 overflow-y-auto grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2.5 content-start bg-slate-50/70 pos-grid" id="productGrid">
                @forelse($catalog as $item)
                    @php
                        $isPaket = $item['type'] === 'paket_wisata';
                        $stock = $item['stock'];
                    @endphp
                    <div class="product-card group bg-white border border-slate-200 hover:border-emerald-600 hover:shadow-md transition cursor-pointer rounded-xl p-2.5 flex flex-col justify-between relative overflow-hidden active:scale-[0.98]"
                         style="min-height: 195px;"
                         data-type="{{ $item['type'] }}"
                         data-id="{{ $item['id'] }}"
                         data-name="{{ $item['name'] }}"
                         data-price="{{ $item['price'] }}"
                         data-stock="{{ $stock ?? 0 }}"
                         data-min-participants="{{ $item['min_participants'] ?? 1 }}"
                         data-category="{{ $item['category'] }}">
                        
                        <!-- Image & Stock Badge -->
                        <div class="relative w-full h-24 sm:h-28 mb-2 rounded-lg bg-slate-100 overflow-hidden border border-slate-100 flex-shrink-0">
                            @if($item['image'])
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80';" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                            @else
                                <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-300">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                </div>
                            @endif

                            @if(!$isPaket)
                                <span class="absolute top-1.5 right-1.5 px-2 py-0.5 rounded text-[10px] font-bold backdrop-blur-md shadow-xs {{ $stock > 10 ? 'bg-slate-900/80 text-white' : ($stock > 0 ? 'bg-amber-600/90 text-white' : 'bg-rose-600/90 text-white') }}">
                                    Stok: {{ $stock }}
                                </span>
                            @else
                                <span class="absolute top-1.5 right-1.5 px-2 py-0.5 rounded text-[10px] font-bold backdrop-blur-md shadow-xs bg-emerald-700/90 text-white">
                                    Paket Wisata
                                </span>
                            @endif
                        </div>

                        <!-- Card Body (Category, Title, Prominent Price Tag) -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider truncate">
                                    {{ $item['sub_label'] }} • {{ $item['category'] }}
                                </div>
                                <h4 class="font-bold text-slate-900 text-xs sm:text-sm leading-snug line-clamp-2 mt-0.5 group-hover:text-emerald-700 transition-colors" title="{{ $item['name'] }}">
                                    {{ $item['name'] }}
                                </h4>
                            </div>

                            <div class="pt-2 border-t border-slate-100 mt-2 flex items-center justify-between">
                                <span class="text-emerald-700 font-black text-xs sm:text-sm md:text-base">
                                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                                    @if($item['type'] === 'paket_wisata')
                                        <span class="text-[9px] text-slate-400 font-semibold">/{{ $item['is_per_orang'] ? 'orang' : 'paket' }}</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-slate-400">
                        <svg width="36" height="36" class="mx-auto mb-2 text-slate-300 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        <p class="text-xs font-semibold">Belum ada item yang aktif.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Section: Fixed Side Cart & Payment (Side-by-Side on Tablet) -->
        <div class="w-full h-[45%] min-h-0 lg:w-96 lg:h-auto flex flex-col bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden flex-shrink-0">
            
            <!-- Cart Header -->
            <div class="p-2.5 px-3 border-b border-slate-200 bg-slate-900 text-white flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-1.5">
                    <svg width="16" height="16" class="text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                    <h3 class="font-bold text-xs sm:text-sm">Keranjang</h3>
                </div>
                <button type="button" id="clearCartBtn" class="px-2 py-0.5 text-[10px] font-medium text-rose-300 hover:text-white hover:bg-rose-900/40 rounded transition border border-rose-800/40">
                    Kosongkan
                </button>
            </div>

            <!-- Cart Scroll Area: Items + Checkout in one scrollable container -->
            <div class="flex-1 overflow-y-auto min-h-0 flex flex-col">

            <!-- Cart Items Container -->
            <div class="flex-1 p-2.5 space-y-1.5 bg-slate-50/50" id="cartItemsList">
                <div id="emptyCartMessage" class="h-full flex flex-col items-center justify-center text-center py-8 text-slate-400">
                    <svg width="32" height="32" class="text-slate-300 mb-1.5 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <p class="text-xs font-semibold text-slate-600">Keranjang Kosong</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Sentuh produk untuk menambahkan</p>
                </div>
            </div>

            <!-- Checkout & Payment Panel -->
            <div class="p-2.5 sm:p-3 border-t border-slate-200 bg-white space-y-2">
                
                <!-- Totals -->
                <div class="space-y-1 text-xs">
                    <div class="flex justify-between text-slate-500 text-[11px]">
                        <span>Total Items:</span>
                        <span id="cartTotalItems" class="font-bold text-slate-800">0 item</span>
                    </div>
                    <div class="flex justify-between items-baseline pt-1 border-t border-slate-100">
                        <span class="text-slate-800 font-bold text-xs sm:text-sm">Total Tagihan:</span>
                        <span id="cartTotalAmount" class="text-emerald-700 font-extrabold text-lg sm:text-xl">Rp 0</span>
                    </div>
                </div>

                <!-- Payment Method Selector Tabs -->
                <div>
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-1" id="paymentMethodContainer">
                        <button type="button" class="pay-method-btn px-1.5 py-1 rounded-md border border-slate-900 bg-slate-900 text-white font-bold text-[11px] flex items-center justify-center transition" data-method="cash">
                            Tunai
                        </button>
                        <button type="button" class="pay-method-btn px-1.5 py-1 rounded-md border border-slate-200 bg-white text-slate-600 font-medium text-[11px] flex items-center justify-center hover:bg-slate-50 transition" data-method="qris">
                            QRIS
                        </button>
                        <button type="button" class="pay-method-btn px-1.5 py-1 rounded-md border border-slate-200 bg-white text-slate-600 font-medium text-[11px] flex items-center justify-center hover:bg-slate-50 transition" data-method="transfer">
                            Transfer
                        </button>
                    </div>
                </div>

                <!-- Cash Payment Details -->
                <div id="cashPaymentSection" class="space-y-1 pt-0.5">
                    <div class="flex justify-between items-center">
                        <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Uang Diterima (Rp)</label>
                        <button type="button" id="exactAmountBtn" class="text-[10px] font-bold text-emerald-700 hover:underline">
                            Uang Pas
                        </button>
                    </div>
                    
                    <input type="text" id="paidAmountInput" placeholder="0" inputmode="numeric" autocomplete="off"
                           class="w-full px-2.5 py-1 text-right font-bold text-sm rounded-md border border-slate-300 focus:ring-2 focus:ring-slate-900 focus:outline-none transition">
                    
                    <!-- Quick Nominal Denominations -->
                    <div class="grid grid-cols-4 gap-1">
                        <button type="button" class="quick-nominal-btn py-0.5 rounded bg-slate-100 border border-slate-200 text-[10px] font-bold text-slate-700 hover:bg-slate-200 transition" data-amount="10000">10k</button>
                        <button type="button" class="quick-nominal-btn py-0.5 rounded bg-slate-100 border border-slate-200 text-[10px] font-bold text-slate-700 hover:bg-slate-200 transition" data-amount="20000">20k</button>
                        <button type="button" class="quick-nominal-btn py-0.5 rounded bg-slate-100 border border-slate-200 text-[10px] font-bold text-slate-700 hover:bg-slate-200 transition" data-amount="50000">50k</button>
                        <button type="button" class="quick-nominal-btn py-0.5 rounded bg-slate-100 border border-slate-200 text-[10px] font-bold text-slate-700 hover:bg-slate-200 transition" data-amount="100000">100k</button>
                    </div>

                    <div class="flex justify-between items-center text-[11px] pt-1 border-t border-slate-100">
                        <span class="font-medium text-slate-600">Kembalian:</span>
                        <span id="changeAmountText" class="font-extrabold text-xs text-slate-900">Rp 0</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="button" id="checkoutBtn" disabled 
                        class="w-full py-2 bg-emerald-700 hover:bg-emerald-800 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold rounded-lg shadow-xs transition flex items-center justify-center gap-1 text-xs tracking-wide">
                    <span>Proses Pembayaran</span>
                </button>
            </div>

            </div><!-- /Cart Scroll Area -->
        </div>
    </div>
</div>

<!-- Visitor Info Modal (required if paket wisata in cart) -->
<div id="visitorModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-5 border border-slate-100">
        <h3 class="text-sm font-bold text-slate-900 mb-0.5">Data Pengunjung</h3>
        <p class="text-[11px] text-slate-500 mb-4">Keranjang berisi paket wisata. Isi data pengunjung untuk membuat tiket booking.</p>

        <div class="space-y-2.5">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Lengkap</label>
                <input type="text" id="visitorNameInput" placeholder="Nama pengunjung" autocomplete="off"
                       class="w-full px-3 py-2 text-xs font-medium rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-600 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">No. WhatsApp</label>
                <input type="text" id="visitorPhoneInput" placeholder="628xxxxxxxxxx" inputmode="numeric" autocomplete="off"
                       class="w-full px-3 py-2 text-xs font-medium rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-600 focus:outline-none transition">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal Kunjungan</label>
                    <input type="date" id="visitorDateInput"
                           class="w-full px-3 py-2 text-xs font-medium rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-600 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Sesi</label>
                    <select id="visitorSessionSelect"
                            class="w-full px-3 py-2 text-xs font-medium rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-600 focus:outline-none transition">
                        @foreach($sessions as $session)
                            <option value="{{ $session->sesi }}">{{ $session->sesi }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <p id="visitorErrorText" class="text-[10px] font-semibold text-rose-600 hidden"></p>
        </div>

        <div class="grid grid-cols-2 gap-2 mt-5">
            <button type="button" id="visitorCancelBtn" class="py-2 rounded-lg border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-100 transition">
                Batal
            </button>
            <button type="button" id="visitorConfirmBtn" class="py-2 rounded-lg bg-emerald-700 text-white font-semibold text-xs hover:bg-emerald-800 transition flex items-center justify-center gap-1">
                Lanjut Bayar
            </button>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div id="receiptModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-5 text-center border border-slate-100">
        <div class="w-10 h-10 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center mx-auto mb-2 text-lg font-bold">
            ✓
        </div>
        <h3 class="text-sm font-bold text-slate-900 mb-0.5">Transaksi Berhasil</h3>
        <p class="text-[11px] text-slate-500 mb-3 font-mono" id="modalInvoiceText">POS-20260809-XXXX</p>
        
        <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 mb-4 text-xs text-left space-y-1">
            <div class="flex justify-between items-center">
                <span class="text-slate-500 font-medium">Uang Kembalian:</span>
                <span class="font-extrabold text-emerald-700 text-sm" id="modalChangeText">Rp 0</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <button type="button" id="closeModalBtn" class="py-1.5 px-3 rounded-lg border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-100 transition">
                Tutup
            </button>
            <a href="#" id="printReceiptBtn" target="_blank" class="py-1.5 px-3 rounded-lg bg-emerald-700 text-white font-semibold text-xs hover:bg-emerald-800 transition flex items-center justify-center gap-1">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Struk</span>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let cart = [];
    let selectedPaymentMethod = 'cash';

    const productCards = document.querySelectorAll('.product-card');
    const typeBtns = document.querySelectorAll('.type-btn');
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

    const receiptModal = document.getElementById('receiptModal');
    const modalInvoiceText = document.getElementById('modalInvoiceText');
    const modalChangeText = document.getElementById('modalChangeText');
    const printReceiptBtn = document.getElementById('printReceiptBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');

    const visitorModal = document.getElementById('visitorModal');
    const visitorNameInput = document.getElementById('visitorNameInput');
    const visitorPhoneInput = document.getElementById('visitorPhoneInput');
    const visitorDateInput = document.getElementById('visitorDateInput');
    const visitorSessionSelect = document.getElementById('visitorSessionSelect');
    const visitorErrorText = document.getElementById('visitorErrorText');
    const visitorCancelBtn = document.getElementById('visitorCancelBtn');
    const visitorConfirmBtn = document.getElementById('visitorConfirmBtn');

    let pendingCheckout = null;

    function openVisitorModal() {
        visitorErrorText.classList.add('hidden');
        visitorErrorText.textContent = '';
        if (!visitorDateInput.value) {
            visitorDateInput.value = new Date().toISOString().slice(0, 10);
        }
        visitorModal.classList.remove('hidden');
    }

    function closeVisitorModal() {
        visitorModal.classList.add('hidden');
    }

    function getRawPaidAmount() {
        const clean = paidAmountInput.value.replace(/[^0-9]/g, '');
        return clean ? parseInt(clean, 10) : 0;
    }

    function updatePaidAmountDisplay(valNum) {
        if (!valNum || valNum <= 0) {
            paidAmountInput.value = '';
        } else {
            paidAmountInput.value = formatRupiah(valNum);
        }
        calculateChange();
    }

    typeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            typeBtns.forEach(b => {
                b.classList.remove('bg-slate-900', 'text-white', 'shadow-xs');
                b.classList.add('bg-slate-100', 'text-slate-600', 'border', 'border-slate-200');
            });
            btn.classList.remove('bg-slate-100', 'text-slate-600', 'border', 'border-slate-200');
            btn.classList.add('bg-slate-900', 'text-white', 'shadow-xs');

            const type = btn.dataset.type;
            filterProducts(type, searchInput.value.toLowerCase());
        });
    });

    searchInput.addEventListener('input', (e) => {
        const activeType = document.querySelector('.type-btn.bg-slate-900')?.dataset.type || 'all';
        filterProducts(activeType, e.target.value.toLowerCase());
    });

    function filterProducts(type, search) {
        productCards.forEach(card => {
            const matchesType = type === 'all' || card.dataset.type === type;
            const matchesSearch = card.dataset.name.toLowerCase().includes(search);
            if (matchesType && matchesSearch) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    productCards.forEach(card => {
        card.addEventListener('click', () => {
            const type = card.dataset.type;
            const id = card.dataset.id;
            const name = card.dataset.name;
            const price = parseFloat(card.dataset.price);
            const stock = parseInt(card.dataset.stock);
            const minPax = parseInt(card.dataset.minParticipants || '1', 10);
            const isPaket = type === 'paket_wisata';

            if (!isPaket && stock <= 0) {
                alert('Stok produk habis!');
                return;
            }

            const existingIndex = cart.findIndex(item => item.type === type && item.id === id);
            if (existingIndex > -1) {
                if (!isPaket && cart[existingIndex].quantity + 1 > stock) {
                    alert('Jumlah melebihi stok yang tersedia (' + stock + ')');
                    return;
                }
                cart[existingIndex].quantity++;
            } else {
                cart.push({ type, id, name, price, stock, isPaket, quantity: isPaket ? minPax : 1, minParticipants: isPaket ? minPax : 1 });
            }

            renderCart();
        });
    });

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
            itemEl.className = 'flex items-center justify-between p-2 rounded-lg border border-slate-200 bg-white text-xs hover:border-slate-300 transition shadow-2xs';
            itemEl.innerHTML = `
                <div class="flex-1 min-w-0 pr-1.5">
                    <h5 class="font-bold text-slate-900 truncate text-xs">${item.name}</h5>
                    <div class="text-slate-500 text-[10px] mt-0.5">
                        ${item.isPaket ? '<span class="text-emerald-700 font-bold uppercase text-[9px]">Paket Wisata</span> ' + (item.minParticipants > 1 ? '<span class="text-slate-400 text-[9px]">(min ' + item.minParticipants + ' org)</span> ' : '') : ''}
                        Rp ${formatRupiah(item.price)} x ${item.quantity} = <strong class="text-emerald-700 font-extrabold">Rp ${formatRupiah(subtotal)}</strong>
                    </div>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0">
                    <button type="button" class="decrease-btn w-5 h-5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center transition active:scale-95" data-index="${index}">-</button>
                    <span class="font-bold text-slate-900 w-4 text-center text-xs">${item.quantity}</span>
                    <button type="button" class="increase-btn w-5 h-5 rounded bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold flex items-center justify-center transition active:scale-95" data-index="${index}">+</button>
                    <button type="button" class="remove-btn w-5 h-5 rounded bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold flex items-center justify-center ml-0.5 transition active:scale-95" data-index="${index}">✕</button>
                </div>
            `;
            cartItemsList.appendChild(itemEl);
        });

        cartTotalItems.textContent = totalQty + ' item';
        cartTotalAmount.textContent = 'Rp ' + formatRupiah(totalPrice);
        checkoutBtn.disabled = false;

        calculateChange();
    }

    cartItemsList.addEventListener('click', (e) => {
        if (e.target.classList.contains('increase-btn')) {
            const index = parseInt(e.target.dataset.index);
            if (!cart[index].isPaket && cart[index].quantity + 1 > cart[index].stock) {
                alert('Stok produk tidak mencukupi');
                return;
            }
            cart[index].quantity++;
            renderCart();
        } else if (e.target.classList.contains('decrease-btn')) {
            const index = parseInt(e.target.dataset.index);
            if (cart[index].isPaket) {
                if (cart[index].quantity > cart[index].minParticipants) {
                    cart[index].quantity--;
                } else {
                    alert('Jumlah minimal paket ' + cart[index].minParticipants + ' orang.');
                }
            } else if (cart[index].quantity > 1) {
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

    paymentMethodBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            paymentMethodBtns.forEach(b => {
                b.classList.remove('border-slate-900', 'bg-slate-900', 'text-white');
                b.classList.add('border-slate-200', 'bg-white', 'text-slate-600');
            });
            btn.classList.remove('border-slate-200', 'bg-white', 'text-slate-600');
            btn.classList.add('border-slate-900', 'bg-slate-900', 'text-white');

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
            changeAmountText.className = 'font-extrabold text-xs text-emerald-700';
        } else {
            changeAmountText.textContent = 'Kurang Rp ' + formatRupiah(Math.abs(change));
            changeAmountText.className = 'font-extrabold text-xs text-rose-600';
        }
    }

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

    checkoutBtn.addEventListener('click', async () => {
        if (cart.length === 0) return;

        const total = getTotalPrice();
        const paid = getRawPaidAmount();

        if (selectedPaymentMethod === 'cash' && paid < total) {
            alert('Jumlah uang pembayaran kurang dari total tagihan!');
            return;
        }

        const hasPaket = cart.some(item => item.isPaket);

        if (hasPaket) {
            pendingCheckout = { total, paid };
            openVisitorModal();
            return;
        }

        await doCheckout(null);
    });

    visitorCancelBtn.addEventListener('click', closeVisitorModal);

    visitorConfirmBtn.addEventListener('click', async () => {
        const name = visitorNameInput.value.trim();
        const phone = visitorPhoneInput.value.trim();
        const visitDate = visitorDateInput.value;
        const sesi = visitorSessionSelect.value;

        if (!name || !phone || !visitDate || !sesi) {
            visitorErrorText.textContent = 'Semua field wajib diisi.';
            visitorErrorText.classList.remove('hidden');
            return;
        }

        if (!/^(0|62)[0-9]{8,15}$/.test(phone)) {
            visitorErrorText.textContent = 'Nomor WhatsApp tidak valid (contoh: 628xxxx).';
            visitorErrorText.classList.remove('hidden');
            return;
        }

        visitorErrorText.classList.add('hidden');
        closeVisitorModal();
        await doCheckout({ name, phone, visitDate, sesi });
    });

    async function doCheckout(visitor) {
        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = `<span>Memproses...</span>`;

        const total = pendingCheckout?.total ?? getTotalPrice();
        const paid = pendingCheckout?.paid ?? getRawPaidAmount();
        pendingCheckout = null;

        const payload = {
            items: cart.map(item => ({ item_type: item.type, item_id: item.id, quantity: item.quantity })),
            paid_amount: selectedPaymentMethod === 'cash' ? paid : total,
            payment_method: selectedPaymentMethod,
            _token: '{{ csrf_token() }}'
        };

        if (visitor) {
            payload.customer_name = visitor.name;
            payload.customer_phone = visitor.phone;
            payload.visit_date = visitor.visitDate;
            payload.sesi = visitor.sesi;
        }

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
                visitorNameInput.value = '';
                visitorPhoneInput.value = '';
                renderCart();
            } else {
                alert(data.message || 'Terjadi kesalahan saat memproses transaksi');
            }
        } catch (err) {
            console.error(err);
            alert('Gagal menghubungi server. Silakan coba lagi.');
        } finally {
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = `<span>Proses Pembayaran</span>`;
        }
    }

    closeModalBtn.addEventListener('click', () => {
        receiptModal.classList.add('hidden');
    });

    function formatRupiah(amount) {
        return new Intl.NumberFormat('id-ID').format(amount);
    }
});
</script>
</body>
</html>
