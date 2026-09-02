@extends('admin.layouts.app')
@section('title', 'Kelola Add-On')

@section('content')
<div class="space-y-6 font-sans">
    <!-- Top Action Bar -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2.5 mb-1">
                <div class="w-9 h-9 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-base flex-shrink-0 shadow-xs">
                    🍱
                </div>
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Kelola Add-On & Fasilitas</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200/80">
                    {{ $totalActive }} aktif
                </span>
            </div>
            <p class="text-xs text-slate-500">Atur paket makanan, sewa alat, pemandu, dan layanan tambahan untuk booking & POS</p>
        </div>
        
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('admin.add-ons.create') }}" class="px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-700/20 transition flex items-center justify-center gap-1.5 flex-1 sm:flex-initial active:scale-[0.98]">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Add-On Baru</span>
            </a>
        </div>
    </div>

    <!-- Alert jika ada item yang terlanjur di-soft-delete dan admin sedang di tab Aktif -->
    @if($totalTrashed > 0 && $tab !== 'trashed')
        <div class="bg-amber-50 border border-amber-200/80 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2.5 text-amber-900 text-xs">
                <span class="text-base flex-shrink-0">⚠️</span>
                <div>
                    <span class="font-bold">Perhatian:</span> Ada <strong>{{ $totalTrashed }} add-on</strong> yang sebelumnya diarsipkan / soft delete dan belum terhapus permanen.
                </div>
            </div>
            <a href="{{ route('admin.add-ons.index', ['tab' => 'trashed']) }}" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition flex-shrink-0 shadow-xs">
                Buka Tab Sampah & Hapus Permanen →
            </a>
        </div>
    @endif

    <!-- Navigation Tabs & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-2">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.add-ons.index', array_merge(request()->except('tab', 'page'), ['tab' => 'active'])) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $tab === 'active' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                <span>Aktif</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'active' ? 'bg-slate-800 text-emerald-300' : 'bg-slate-100 text-slate-500' }}">
                    {{ $totalActive }}
                </span>
            </a>

            <a href="{{ route('admin.add-ons.index', array_merge(request()->except('tab', 'page'), ['tab' => 'trashed'])) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $tab === 'trashed' ? 'bg-rose-900 text-white shadow-xs' : ($totalTrashed > 0 ? 'bg-amber-50 text-amber-800 border border-amber-300 hover:bg-amber-100' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200') }}">
                <span>Sampah / Terhapus</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'trashed' ? 'bg-rose-800 text-white' : ($totalTrashed > 0 ? 'bg-amber-200 text-amber-900 font-black' : 'bg-slate-100 text-slate-500') }}">
                    {{ $totalTrashed }}
                </span>
            </a>

            <a href="{{ route('admin.add-ons.index', array_merge(request()->except('tab', 'page'), ['tab' => 'all'])) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $tab === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                <span>Semua Data</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'all' ? 'bg-slate-800 text-slate-300' : 'bg-slate-100 text-slate-500' }}">
                    {{ $totalAll }}
                </span>
            </a>
        </div>

        @if($tab === 'trashed' && $totalTrashed > 0)
            <form method="POST" action="{{ route('admin.add-ons.trash.empty') }}" onsubmit="return confirm('Kosongkan semua sampah? {{ $totalTrashed }} add-on ini akan DIHAPUS PERMANEN selamanya.')">
                @csrf @method('DELETE')
                <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-xs">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span>Kosongkan Semua Sampah ({{ $totalTrashed }})</span>
                </button>
            </form>
        @endif
    </div>

    <!-- Search & Filter Box -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.add-ons.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-2.5">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="relative sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama add-on atau kategori..." class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                <svg width="15" height="15" class="text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <div>
                <select name="kategori" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none transition bg-white">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('kategori') === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="tipe_harga" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none transition bg-white">
                    <option value="">-- Model Harga --</option>
                    <option value="per_orang" {{ request('tipe_harga') === 'per_orang' ? 'selected' : '' }}>Per Orang</option>
                    <option value="per_unit" {{ request('tipe_harga') === 'per_unit' ? 'selected' : '' }}>Per Unit</option>
                </select>
            </div>

            <div class="flex gap-1.5">
                <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition flex-1 flex items-center justify-center gap-1">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>Filter</span>
                </button>
                <a href="{{ route('admin.add-ons.index', ['tab' => $tab]) }}" class="px-3 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200 transition flex items-center justify-center" title="Reset filter">
                    ↺
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="p-4">Item Add-On</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4">Model Harga</th>
                        <th class="p-4">Harga Satuan</th>
                        <th class="p-4 text-center">Urutan</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($addOns as $addOn)
                        <tr class="hover:bg-slate-50/60 transition {{ $addOn->trashed() ? 'bg-amber-50/30' : '' }}">
                            <!-- Thumbnail & Nama -->
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200/70 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                        @if(!empty($addOn->gambar))
                                            <img src="{{ $addOn->gambar }}" alt="{{ $addOn->nama }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-xl">🍱</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 text-xs sm:text-sm truncate">{{ $addOn->nama }}</div>
                                        @if(!empty($addOn->deskripsi))
                                            <p class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5">{{ $addOn->deskripsi }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Kategori -->
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200/80 capitalize">
                                    {{ $addOn->kategori ?: 'Umum' }}
                                </span>
                            </td>

                            <!-- Model Harga -->
                            <td class="p-4">
                                @if($addOn->tipe_harga === 'per_orang')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                        <span>👤</span> Per Orang
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        <span>📦</span> Per Unit
                                    </span>
                                @endif
                            </td>

                            <!-- Harga Satuan -->
                            <td class="p-4">
                                <span class="font-extrabold text-emerald-700 text-xs sm:text-sm">
                                    Rp {{ number_format($addOn->harga, 0, ',', '.') }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-semibold block">
                                    /{{ $addOn->labelSatuan() }}
                                </span>
                            </td>

                            <!-- Urutan -->
                            <td class="p-4 text-center">
                                <span class="px-2 py-0.5 rounded-md font-mono text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200/60">
                                    #{{ $addOn->urutan ?? '-' }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="p-4 text-center">
                                @if($addOn->trashed())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        <span>🗑️</span> Terhapus
                                    </span>
                                @elseif($addOn->aktif)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($addOn->trashed())
                                        <!-- Tombol Restore -->
                                        <form method="POST" action="{{ route('admin.add-ons.restore', $addOn->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold transition flex items-center gap-1 shadow-xs" title="Pulihkan data">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                                <span>Pulihkan</span>
                                            </button>
                                        </form>

                                        <!-- Tombol Hapus Permanen -->
                                        <form method="POST" action="{{ route('admin.add-ons.force-delete', $addOn->id) }}" onsubmit="return confirm('HAPUS PERMANEN: Add-on \'{{ $addOn->nama }}\' akan dihapus selamanya dari database. Lanjutkan?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-[11px] font-bold transition flex items-center gap-1 shadow-xs" title="Hapus selamanya">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                <span>Hapus Permanen</span>
                                            </button>
                                        </form>
                                    @else
                                        <!-- Tombol Edit -->
                                        <a href="{{ route('admin.add-ons.edit', $addOn->id) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-bold transition flex items-center gap-1 border border-slate-200" title="Edit add-on">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            <span>Edit</span>
                                        </a>

                                        <!-- Tombol Hapus Langsung Permanen -->
                                        <form method="POST" action="{{ route('admin.add-ons.destroy', $addOn->id) }}" onsubmit="return confirm('Hapus permanen add-on \'{{ $addOn->nama }}\'? Data akan dihapus selamanya.')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-[11px] font-bold transition flex items-center gap-1 border border-rose-200/60" title="Hapus add-on">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl font-bold">
                                    🍱
                                </div>
                                <h3 class="text-sm font-bold text-slate-700">Belum ada add-on yang ditemukan</h3>
                                <p class="text-xs text-slate-400 mt-1">
                                    @if($tab === 'trashed')
                                        Tempat sampah kosong. Tidak ada add-on yang terhapus.
                                    @else
                                        Mulai tambahkan add-on baru atau coba ubah kata kunci filter Anda.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($addOns->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $addOns->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
