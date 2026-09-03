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
        .custom-scroll::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .pos-grid {
            grid-auto-rows: max-content !important;
        }
        .pay-method-btn {
            border: 1.5px solid #cbd5e1 !important;
            background: #ffffff !important;
            color: #334155 !important;
            transition: all 0.15s ease-in-out;
        }
        .pay-method-btn:hover {
            border-color: #94a3b8 !important;
            background: #f8fafc !important;
        }
        .pay-method-btn.active {
            border-color: #0f172a !important;
            background: #0f172a !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2) !important;
        }
        .pay-method-btn.active svg {
            color: #34d399 !important;
        }
        .pay-method-btn.active span {
            color: #ffffff !important;
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

<div class="h-screen max-h-screen flex flex-col p-1.5 sm:p-2 md:p-2.5 gap-1.5 sm:gap-2 bg-slate-100 overflow-hidden" id="posContainer">
    <!-- Main Workspace: Side-by-Side 2 Columns (Always on the Right) -->
    <div class="flex-1 flex flex-row gap-1.5 sm:gap-2 min-h-0 w-full overflow-hidden">
        
        <!-- Left Section: Catalog (Category Pills Wrap Cleanly, Search Bar, Product Grid) -->
        <div class="flex-1 flex flex-col bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden min-w-0 min-h-0">
            
            <!-- Category Filter & Search Header -->
            <div class="p-2 sm:p-2.5 border-b border-slate-200 bg-white flex flex-col gap-1.5 flex-shrink-0">
                
                <!-- Row 1: Back Button & Search Input in ONE Flex Row -->
                <div class="flex items-center gap-2 w-full">
                    <a href="{{ route('admin.dashboard') }}" title="Kembali ke Dashboard"
                       class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-900 hover:bg-slate-800 text-white flex items-center justify-center shadow-xs transition active:scale-95 cursor-pointer">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div class="relative flex-1">
                        <input type="text" id="searchInput" placeholder="Cari nama produk, paket, atau SKU..." 
                               class="w-full pl-8 pr-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 text-xs focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition">
                        <svg width="14" height="14" class="text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>

                <!-- Row 2: Category Filter Pills (Single Row Horizontal Scroll) -->
                <div class="flex items-center gap-1.5 w-full py-0.5 overflow-x-auto no-scrollbar flex-nowrap">
                    <button type="button" class="type-btn px-2.5 py-1 rounded-lg text-[11px] font-semibold transition bg-slate-900 text-white shadow-xs whitespace-nowrap flex-shrink-0 cursor-pointer active:scale-95" data-type="all">
                        Semua ({{ $catalog->count() }})
                    </button>
                    <button type="button" class="type-btn px-2.5 py-1 rounded-lg text-[11px] font-medium transition bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200 whitespace-nowrap flex-shrink-0 cursor-pointer active:scale-95" data-type="paket_wisata">
                        Paket Wisata ({{ $catalog->where('type', 'paket_wisata')->count() }})
                    </button>
                    <button type="button" class="type-btn px-2.5 py-1 rounded-lg text-[11px] font-medium transition bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200 whitespace-nowrap flex-shrink-0 cursor-pointer active:scale-95" data-type="addon">
                        Add-On ({{ $catalog->where('type', 'addon')->count() }})
                    </button>
                    <button type="button" class="type-btn px-2.5 py-1 rounded-lg text-[11px] font-medium transition bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200 whitespace-nowrap flex-shrink-0 cursor-pointer active:scale-95" data-type="pos_product">
                        Produk POS ({{ $catalog->where('type', 'pos_product')->count() }})
                    </button>
                </div>
            </div>

            <!-- Product Cards Catalog Grid (Minimal 3 Kolom) -->
            <div class="flex-1 p-2 sm:p-2.5 overflow-y-auto custom-scroll grid grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2 sm:gap-2.5 content-start bg-slate-50/70 pos-grid" id="productGrid">
                @forelse($catalog as $item)
                    @php
                        $isPaket = $item['type'] === 'paket_wisata';
                        $isAddon = $item['type'] === 'addon';
                        $stock = $item['stock'];
                    @endphp
                    <div class="product-card group bg-white border border-slate-200 hover:border-emerald-600 hover:shadow-md transition-all cursor-pointer rounded-xl p-2 flex flex-col justify-between relative overflow-hidden active:scale-[0.97]"
                         data-type="{{ $item['type'] }}"
                         data-id="{{ $item['id'] }}"
                         data-name="{{ $item['name'] }}"
                         data-price="{{ $item['price'] }}"
                         data-stock="{{ $stock ?? 0 }}"
                         data-min-participants="{{ $item['min_participants'] ?? 1 }}"
                         data-category="{{ $item['category'] }}">
                        
                        <!-- Image & Stock Badge -->
                        <div class="relative w-full h-16 sm:h-18 lg:h-20 mb-1.5 rounded-lg bg-slate-100 overflow-hidden border border-slate-100 flex-shrink-0">
                            @if($item['image'])
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80';" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                            @else
                                <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-300">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                </div>
                            @endif

                            @if($isPaket)
                                <span class="absolute top-1 right-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold backdrop-blur-md shadow-xs bg-emerald-700/90 text-white">
                                    Paket
                                </span>
                            @elseif($isAddon)
                                <span class="absolute top-1 right-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold backdrop-blur-md shadow-xs bg-indigo-700/90 text-white">
                                    Add-On
                                </span>
                            @else
                                <span class="absolute top-1 right-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold backdrop-blur-md shadow-xs {{ $stock > 10 ? 'bg-slate-900/80 text-white' : ($stock > 0 ? 'bg-amber-600/90 text-white' : 'bg-rose-600/90 text-white') }}">
                                    Stok: {{ $stock }}
                                </span>
                            @endif
                        </div>

                        <!-- Card Body (Category, Title, Prominent Price Tag) -->
                        <div class="flex-1 flex flex-col justify-between min-h-0">
                            <div>
                                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate">
                                    {{ $item['sub_label'] }} • {{ $item['category'] }}
                                </div>
                                <h4 class="font-bold text-slate-900 text-xs leading-snug line-clamp-2 mt-0.5 group-hover:text-emerald-700 transition-colors" title="{{ $item['name'] }}">
                                    {{ $item['name'] }}
                                </h4>
                            </div>

                            <div class="pt-1.5 border-t border-slate-100 mt-1.5 flex items-center justify-between">
                                <span class="text-emerald-700 font-black text-xs sm:text-sm">
                                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                                    @if($item['type'] === 'paket_wisata')
                                        <span class="text-[8px] text-slate-400 font-semibold">/{{ $item['is_per_orang'] ? 'org' : 'pkt' }}</span>
                                    @elseif($item['type'] === 'addon')
                                        <span class="text-[8px] text-slate-400 font-semibold">/{{ $item['is_per_orang'] ? 'org' : 'unt' }}</span>
                                    @endif
                                </span>
                                <span class="w-5 h-5 rounded-full bg-emerald-50 text-emerald-700 group-hover:bg-emerald-700 group-hover:text-white flex items-center justify-center text-xs font-bold transition-colors shadow-2xs">
                                    +
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 text-slate-400 text-xs sm:text-sm">
                        Belum ada item katalog yang aktif.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Section: Fixed Side Cart & Payment (Always on the Right) -->
        <div class="w-[260px] sm:w-[280px] md:w-[295px] lg:w-[325px] xl:w-96 h-full min-h-0 flex flex-col bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden flex-shrink-0">
            
            <!-- Cart Header (Fixed Top) -->
            <div class="py-2 px-3 border-b border-slate-200 bg-slate-900 text-white flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-1.5">
                    <svg width="15" height="15" class="text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                    <h3 class="font-bold text-xs sm:text-sm">Keranjang</h3>
                </div>
                <button type="button" id="clearCartBtn" class="px-2.5 py-1 text-[11px] font-semibold text-rose-300 hover:text-white hover:bg-rose-900/50 rounded-lg transition border border-rose-800/50 active:scale-95 cursor-pointer">
                    Kosongkan
                </button>
            </div>

            <!-- Cart Items Container (Scrollable Middle Area ONLY) -->
            <div class="flex-1 p-2 space-y-1.5 bg-slate-50/50 overflow-y-auto min-h-0 custom-scroll flex flex-col" id="cartItemsList">
                <div id="emptyCartMessage" class="flex-1 flex flex-col items-center justify-center text-center p-3 text-slate-400 my-auto select-none">
                    <div class="w-10 h-10 rounded-full bg-slate-200/70 flex items-center justify-center mb-1.5 text-slate-400">
                        <svg width="20" height="20" class="stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700">Keranjang Kosong</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Sentuh produk untuk menambahkan</p>
                </div>
            </div>

            <!-- Checkout & Payment Panel (Fixed at Bottom - ALWAYS VISIBLE!) -->
            <div class="p-2 sm:p-2.5 border-t border-slate-200 bg-white space-y-1 sm:space-y-1.5 flex-shrink-0 shadow-xs">
                
                <!-- Totals: 1 clean concise row -->
                <div class="flex items-baseline justify-between pb-1 border-b border-slate-100">
                    <div>
                        <span class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-400 tracking-wider">Total Tagihan</span>
                        <span id="cartTotalItems" class="ml-1 text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">0 item</span>
                    </div>
                    <div id="cartTotalAmount" class="text-emerald-700 font-black text-base sm:text-lg tracking-tight">Rp 0</div>
                </div>

                <!-- Payment Method Selector Tabs -->
                <div>
                    <label class="block text-[8px] sm:text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-1.5" id="paymentMethodContainer">
                        <button type="button" class="pay-method-btn active px-1 py-1.5 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 transition shadow-2xs active:scale-95 cursor-pointer" data-method="cash" style="background-color: #0f172a; border-color: #0f172a; color: #ffffff;">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #34d399;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span style="color: #ffffff;">Tunai</span>
                        </button>
                        <button type="button" class="pay-method-btn px-1 py-1.5 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition shadow-2xs active:scale-95 cursor-pointer" data-method="qris" style="background-color: #ffffff; border-color: #cbd5e1; color: #334155;">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            <span style="color: #334155;">QRIS</span>
                        </button>
                        <button type="button" class="pay-method-btn px-1 py-1.5 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition shadow-2xs active:scale-95 cursor-pointer" data-method="transfer" style="background-color: #ffffff; border-color: #cbd5e1; color: #334155;">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            <span style="color: #334155;">Transfer</span>
                        </button>
                    </div>
                </div>

                <!-- 1. Cash Payment Details -->
                <div id="cashPaymentSection" class="space-y-1 pt-0.5">
                    <div class="flex items-center gap-1.5">
                        <div class="relative flex-1">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[11px] font-bold text-slate-400">Rp</span>
                            <input type="text" id="paidAmountInput" placeholder="0" inputmode="numeric" autocomplete="off"
                                   class="w-full pl-8 pr-2 py-0.5 sm:py-1 text-right font-black text-xs sm:text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 focus:outline-none transition">
                        </div>
                        <button type="button" id="exactAmountBtn" class="px-2 py-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition border border-emerald-200 whitespace-nowrap active:scale-95 cursor-pointer">
                            Uang Pas
                        </button>
                    </div>
                    
                    <!-- Quick Nominal Denominations -->
                    <div class="grid grid-cols-4 gap-1">
                        <button type="button" class="quick-nominal-btn py-1 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-200 text-[10px] font-bold text-slate-700 transition active:scale-95 cursor-pointer" data-amount="10000">10k</button>
                        <button type="button" class="quick-nominal-btn py-1 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-200 text-[10px] font-bold text-slate-700 transition active:scale-95 cursor-pointer" data-amount="20000">20k</button>
                        <button type="button" class="quick-nominal-btn py-1 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-200 text-[10px] font-bold text-slate-700 transition active:scale-95 cursor-pointer" data-amount="50000">50k</button>
                        <button type="button" class="quick-nominal-btn py-1 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-200 text-[10px] font-bold text-slate-700 transition active:scale-95 cursor-pointer" data-amount="100000">100k</button>
                    </div>

                    <div class="flex justify-between items-center text-[10px] sm:text-[11px] pt-0.5 border-t border-slate-100">
                        <span class="font-medium text-slate-500">Kembalian:</span>
                        <span id="changeAmountText" class="font-black text-xs text-slate-900">Rp 0</span>
                    </div>
                </div>

                <!-- 2. QRIS Payment Details -->
                <div id="qrisPaymentSection" class="space-y-1.5 pt-0.5 hidden">
                    <div class="p-2 rounded-xl bg-emerald-50/70 border border-emerald-200 flex items-center gap-2">
                        <div class="w-13 h-13 sm:w-14 sm:h-14 bg-white rounded-lg border border-emerald-200 p-1 flex-shrink-0 flex items-center justify-center overflow-hidden shadow-2xs cursor-pointer group" onclick="openQrisModal()" title="Klik untuk perbesar QR Code">
                            @if(!empty($paymentSettings['qris_image']))
                                <img src="{{ $paymentSettings['qris_image'] }}" alt="QRIS" class="w-full h-full object-contain group-hover:scale-105 transition-transform">
                            @else
                                <div class="text-center">
                                    <svg class="w-6 h-6 mx-auto text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                    <span class="text-[7px] font-extrabold text-emerald-800 uppercase block">QRIS</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-[9px] font-extrabold text-emerald-800 uppercase tracking-wider">Scan Barcode QRIS</span>
                                <button type="button" onclick="openQrisModal()" class="text-[9px] font-bold text-emerald-700 hover:text-emerald-900 bg-white border border-emerald-300 px-1.5 py-0.5 rounded shadow-2xs cursor-pointer flex items-center gap-0.5 active:scale-95">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                                    <span>Tampilkan QR</span>
                                </button>
                            </div>
                            <p class="text-[9px] text-slate-600 mt-0.5 leading-tight">Minta pembeli scan QRIS via m-banking atau e-wallet.</p>
                        </div>
                    </div>
                </div>

                <!-- 3. Bank Transfer Payment Details -->
                <div id="transferPaymentSection" class="space-y-1.5 pt-0.5 hidden">
                    <div class="p-2 rounded-xl bg-purple-50/70 border border-purple-200 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-extrabold uppercase tracking-wider text-purple-800">Transfer Rekening Bank</span>
                            <span class="text-[10px] font-bold text-purple-900 bg-white px-1.5 py-0.2 rounded border border-purple-200">{{ $paymentSettings['rekening_bank'] }}</span>
                        </div>
                        <div class="flex items-center justify-between bg-white px-2 py-1 rounded-lg border border-purple-200">
                            <div>
                                <div class="text-[8px] text-slate-400 font-semibold uppercase">No. Rekening</div>
                                <div class="font-mono font-bold text-xs text-slate-900" id="bankAccountNumberText">{{ $paymentSettings['rekening_no'] }}</div>
                            </div>
                            <button type="button" id="copyBankNumberBtn" class="px-2 py-0.5 text-[10px] font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded transition shadow-2xs cursor-pointer flex items-center gap-1 active:scale-95">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                <span id="copyBtnText">Salin</span>
                            </button>
                        </div>
                        <div class="text-[9px] text-slate-600">
                            A.n. <strong class="text-slate-800">{{ $paymentSettings['rekening_atas_nama'] }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="button" id="checkoutBtn" disabled 
                        class="w-full py-2 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5 text-xs tracking-wide cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Proses Pembayaran</span>
                </button>
            </div>
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
                <input type="text" id="visitorPhoneInput" placeholder="08xxxxxxxxxx" inputmode="numeric" maxlength="15" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 15)" autocomplete="off"
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

<!-- QRIS Zoom Modal for Customer Display -->
<div id="qrisModal" class="fixed inset-0 bg-slate-950/85 backdrop-blur-sm z-50 flex flex-col items-center justify-center hidden p-2 sm:p-4 select-none" onclick="closeQrisModal(event)">
    <div class="relative flex flex-col items-center max-h-[96vh] max-w-lg w-auto" onclick="event.stopPropagation()">
        
        <!-- Floating Close Button Top Right -->
        <button type="button" onclick="closeQrisModal()" title="Tutup"
                class="absolute -top-3 -right-3 sm:-top-3.5 sm:-right-3.5 w-8 h-8 rounded-full bg-slate-900 hover:bg-slate-800 text-white flex items-center justify-center shadow-xl transition cursor-pointer z-20 border border-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <!-- 1. GAMBAR FULL QRIS -->
        <div class="bg-white p-1 sm:p-1.5 rounded-2xl shadow-2xl border border-slate-200 flex items-center justify-center overflow-hidden">
            @if(!empty($paymentSettings['qris_image']))
                <img id="qrisModalImg" src="{{ $paymentSettings['qris_image'] }}" alt="QRIS" 
                     class="max-h-[72vh] sm:max-h-[78vh] w-auto max-w-[90vw] object-contain rounded-xl">
            @else
                <div class="p-6 text-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode('https://desawisatagetas.id') }}" alt="QRIS" class="w-52 h-52 mx-auto object-contain">
                    <span class="text-xs font-bold text-slate-700 block mt-2">QRIS Desa Wisata Getas</span>
                </div>
            @endif
        </div>

        <!-- 2. BAWAH: NOMINAL YANG HARUS DIBAYAR (CENTERED & CLEAN) -->
        <div class="mt-2.5 bg-slate-900/90 backdrop-blur-md text-white py-2 px-6 rounded-full border border-slate-800 shadow-xl flex items-center justify-center gap-2">
            <span class="text-xs text-slate-400">Total:</span>
            <span class="font-bold text-white text-base tracking-wide" id="modalQrisAmount">Rp 0</span>
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
    const qrisPaymentSection = document.getElementById('qrisPaymentSection');
    const transferPaymentSection = document.getElementById('transferPaymentSection');
    const copyBankNumberBtn = document.getElementById('copyBankNumberBtn');
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
            const isAddon = type === 'addon';

            if (!isPaket && !isAddon && stock <= 0) {
                alert('Stok produk habis!');
                return;
            }

            const existingIndex = cart.findIndex(item => item.type === type && item.id === id);
            if (existingIndex > -1) {
                if (!isPaket && !isAddon && cart[existingIndex].quantity + 1 > stock) {
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
            itemEl.className = 'flex items-center justify-between p-2 rounded-xl border border-slate-200 bg-white text-xs hover:border-slate-300 transition shadow-2xs gap-1.5';
            itemEl.innerHTML = `
                <div class="flex-1 min-w-0 pr-1">
                    <h5 class="font-bold text-slate-900 truncate text-xs">${item.name}</h5>
                    <div class="text-slate-500 text-[10px] mt-0.5">
                        ${item.isPaket ? '<span class="text-emerald-700 font-bold uppercase text-[9px]">Paket Wisata</span> ' + (item.minParticipants > 1 ? '<span class="text-slate-400 text-[9px]">(min ' + item.minParticipants + ' org)</span> ' : '') : (item.type === 'addon' ? '<span class="text-indigo-700 font-bold uppercase text-[9px]">Add-On</span> ' : '')}
                        Rp ${formatRupiah(item.price)} x ${item.quantity} = <strong class="text-emerald-700 font-extrabold">Rp ${formatRupiah(subtotal)}</strong>
                    </div>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0">
                    <button type="button" class="decrease-btn w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center transition active:scale-90 text-xs cursor-pointer select-none" data-index="${index}" title="Kurangi">-</button>
                    <span class="font-bold text-slate-900 w-5 text-center text-xs">${item.quantity}</span>
                    <button type="button" class="increase-btn w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold flex items-center justify-center transition active:scale-90 text-xs cursor-pointer select-none" data-index="${index}" title="Tambah">+</button>
                    <button type="button" class="remove-btn w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold flex items-center justify-center ml-0.5 transition active:scale-90 text-xs cursor-pointer select-none" data-index="${index}" title="Hapus">✕</button>
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
            if (!cart[index].isPaket && cart[index].type !== 'addon' && cart[index].quantity + 1 > cart[index].stock) {
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

    function selectPaymentMethod(method) {
        selectedPaymentMethod = method;

        paymentMethodBtns.forEach(btn => {
            const isMatch = btn.dataset.method === method;
            const icon = btn.querySelector('svg');
            const text = btn.querySelector('span');

            if (isMatch) {
                btn.classList.add('active');
                btn.style.setProperty('background', '#0f172a', 'important');
                btn.style.setProperty('border-color', '#0f172a', 'important');
                btn.style.setProperty('color', '#ffffff', 'important');
                if (icon) icon.style.setProperty('color', '#34d399', 'important');
                if (text) text.style.setProperty('color', '#ffffff', 'important');
            } else {
                btn.classList.remove('active');
                btn.style.setProperty('background', '#ffffff', 'important');
                btn.style.setProperty('border-color', '#cbd5e1', 'important');
                btn.style.setProperty('color', '#334155', 'important');
                if (icon) icon.style.setProperty('color', '#94a3b8', 'important');
                if (text) text.style.setProperty('color', '#334155', 'important');
            }
        });

        cashPaymentSection.classList.add('hidden');
        if (qrisPaymentSection) qrisPaymentSection.classList.add('hidden');
        if (transferPaymentSection) transferPaymentSection.classList.add('hidden');

        if (method === 'cash') {
            cashPaymentSection.classList.remove('hidden');
        } else if (method === 'qris') {
            if (qrisPaymentSection) qrisPaymentSection.classList.remove('hidden');
        } else if (method === 'transfer') {
            if (transferPaymentSection) transferPaymentSection.classList.remove('hidden');
        }

        calculateChange();
    }

    paymentMethodBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            selectPaymentMethod(btn.dataset.method);
        });
    });

    selectPaymentMethod('cash');

    if (copyBankNumberBtn) {
        copyBankNumberBtn.addEventListener('click', () => {
            const no = document.getElementById('bankAccountNumberText')?.textContent?.trim() || '';
            if (!no) return;
            navigator.clipboard.writeText(no).then(() => {
                const btnText = document.getElementById('copyBtnText');
                if (btnText) {
                    btnText.textContent = 'Disalin!';
                    setTimeout(() => { btnText.textContent = 'Salin'; }, 2000);
                }
            }).catch(() => {
                alert('No. Rekening: ' + no);
            });
        });
    }

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
            visitorErrorText.textContent = 'Nomor WhatsApp tidak valid (contoh: 08xxxxxxxxxx).';
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

    window.openQrisModal = function() {
        const total = getTotalPrice();
        const modalAmount = document.getElementById('modalQrisAmount');
        if (modalAmount) {
            modalAmount.textContent = 'Rp ' + formatRupiah(total);
        }
        document.getElementById('qrisModal')?.classList.remove('hidden');
    };

    window.closeQrisModal = function(e) {
        if (e && e.target && e.target.id !== 'qrisModal' && !e.target.closest('#qrisModal button')) {
            return;
        }
        document.getElementById('qrisModal')?.classList.add('hidden');
    };

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.getElementById('qrisModal')?.classList.add('hidden');
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
</body>
</html>
