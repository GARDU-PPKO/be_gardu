@extends('admin.layouts.app')

@section('title', 'Riwayat Transaksi POS')

@section('content')
<div class="space-y-6 font-sans">
    <!-- Top Action Bar -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2.5 mb-0.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <h2 class="text-lg font-bold text-slate-900">Riwayat Transaksi POS</h2>
            </div>
            <p class="text-xs text-slate-500">Laporan transaksi kasir langsung dan ringkasan omset harian</p>
        </div>
        <a href="{{ route('admin.pos.index') }}" class="px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-700/20 transition flex items-center gap-1.5 active:scale-[0.98]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            <span>Buka Terminal Kasir</span>
        </a>
    </div>

    <!-- Daily Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Omset Hari Ini</div>
                <div class="text-xl font-black text-emerald-700">
                    Rp {{ number_format($todayRevenue, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 border border-blue-100 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <div>
                <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Jumlah Transaksi</div>
                <div class="text-xl font-black text-slate-900">
                    {{ number_format($todayTransactionsCount, 0, ',', '.') }} <span class="text-xs text-slate-400 font-medium">Penjualan</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center gap-4 sm:col-span-2 lg:col-span-1">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-700 border border-purple-100 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Tanggal Hari Ini</div>
                <div class="text-sm font-bold text-slate-900">
                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.pos.transactions.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No Invoice..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <input type="date" name="date" value="{{ request('date') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">

            <select name="payment_method" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                <option value="">-- Semua Metode --</option>
                <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                <option value="qris" {{ request('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition flex-1">Cari</button>
                <a href="{{ route('admin.pos.transactions.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="p-4">No Invoice</th>
                        <th class="p-4">Waktu Transaksi</th>
                        <th class="p-4">Kasir</th>
                        <th class="p-4">Item Dibelanja</th>
                        <th class="p-4">Total Belanja</th>
                        <th class="p-4">Metode</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="p-4 font-bold font-mono text-emerald-800">{{ $t->invoice_number }}</td>
                            <td class="p-4 text-slate-600">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-4 font-semibold text-slate-800">{{ $t->user->nama ?? ($t->user->name ?? 'Kasir') }}</td>
                            <td class="p-4">
                                <div class="max-w-xs space-y-0.5">
                                    @foreach($t->items as $item)
                                        <div class="text-[11px] text-slate-700 flex items-start gap-1">
                                            <span>•</span>
                                            <span class="flex-1">{{ $item->product_name }} <span class="font-bold text-slate-500">({{ $item->quantity }}x)</span></span>
                                            @if($item->item_type === 'paket_wisata')
                                                <span class="shrink-0 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">Tiket</span>
                                            @elseif($item->item_type === 'umkm_product')
                                                <span class="shrink-0 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-amber-50 text-amber-700 border border-amber-200">UMKM</span>
                                            @else
                                                <span class="shrink-0 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-slate-100 text-slate-500 border border-slate-200">POS</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="p-4 font-black text-slate-900 text-sm">
                                Rp {{ number_format($t->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border {{ $t->payment_method === 'cash' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($t->payment_method === 'qris' ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-purple-50 text-purple-800 border-purple-200') }}">
                                    {{ $t->payment_method }}
                                </span>
                                <span class="ml-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border {{ $t->status === 'completed' ? 'bg-slate-50 text-slate-700 border-slate-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                    {{ $t->status }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.pos.receipt', $t->id) }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 font-bold rounded-lg transition inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        <span>Struk</span>
                                    </a>
                                    @if($t->status === 'completed')
                                        <form method="POST" action="{{ route('admin.pos.cancel', $t->id) }}" onsubmit="return confirm('Batalkan transaksi ini? Stok & booking terkait akan dikembalikan.');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-lg transition">
                                                Batalkan
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-slate-400">
                                <p class="text-xs font-semibold">Belum ada data transaksi POS.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/30">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
