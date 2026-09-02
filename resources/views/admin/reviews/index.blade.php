@extends('admin.layouts.app')
@section('title', 'Kelola Ulasan Pengunjung')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Ulasan & Rating Pengunjung</h2>
            <p class="text-sm text-gray-500 mt-0.5">Kelola ulasan dan penilaian pengunjung untuk setiap paket wisata</p>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Ulasan</p>
                <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $totalReviews }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl">
                💬
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Rata-rata Rating</p>
                <div class="flex items-center gap-2 mt-1">
                    <p class="text-2xl font-extrabold text-amber-500">{{ number_format($avgRating, 1) }}</p>
                    <span class="text-amber-400 text-xl">★</span>
                    <span class="text-xs text-gray-400">/ 5.0</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 text-xl">
                ⭐
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Paket Terulas</p>
                <p class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $packages->count() }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-xl">
                🎫
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Filter Paket Wisata</label>
                <select name="package_id" class="w-full text-sm rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="all">Semua Paket</option>
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}" {{ (string)$selectedPackage === (string)$pkg->id ? 'selected' : '' }}>
                            {{ $pkg->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Filter Bintang</label>
                <select name="rating" class="w-full text-sm rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="all">Semua Rating</option>
                    <option value="5" {{ (string)$selectedRating === '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5 Bintang)</option>
                    <option value="4" {{ (string)$selectedRating === '4' ? 'selected' : '' }}>⭐⭐⭐⭐ (4 Bintang)</option>
                    <option value="3" {{ (string)$selectedRating === '3' ? 'selected' : '' }}>⭐⭐⭐ (3 Bintang)</option>
                    <option value="2" {{ (string)$selectedRating === '2' ? 'selected' : '' }}>⭐⭐ (2 Bintang)</option>
                    <option value="1" {{ (string)$selectedRating === '1' ? 'selected' : '' }}>⭐ (1 Bintang)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Status Visibilitas</label>
                <select name="visibility" class="w-full text-sm rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="all">Semua Status</option>
                    <option value="1" {{ (string)$selectedVisibility === '1' ? 'selected' : '' }}>Ditampilkan (Aktif)</option>
                    <option value="0" {{ (string)$selectedVisibility === '0' ? 'selected' : '' }}>Disembunyikan</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold rounded-xl transition">
                    Terapkan Filter
                </button>
                <a href="{{ route('admin.reviews.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold rounded-xl transition flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Reviews Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50/80 text-gray-500 text-xs uppercase font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3.5">Pengulas</th>
                        <th class="px-5 py-3.5">Paket Wisata</th>
                        <th class="px-5 py-3.5">Rating & Komentar</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($reviews as $r)
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="px-5 py-4">
                            <div class="font-bold text-gray-900">{{ $r->nama_pengulas }}</div>
                            @if($r->booking)
                                <a href="{{ route('admin.bookings.show', $r->booking->id) }}" class="text-xs font-mono text-emerald-600 hover:underline">
                                    {{ $r->booking->booking_code }}
                                </a>
                            @else
                                <span class="text-xs text-gray-400">Pengunjung Langsung</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="font-medium text-gray-800">{{ $r->paketWisata->nama ?? '-' }}</span>
                        </td>
                        <td class="px-5 py-4 max-w-md">
                            <div class="flex items-center gap-1 text-amber-400 mb-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <span>{{ $i <= $r->rating ? '★' : '☆' }}</span>
                                @endfor
                                <span class="text-xs font-bold text-gray-700 ml-1">({{ $r->rating }}/5)</span>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed bg-gray-50 rounded-lg p-2.5 border border-gray-100 italic">
                                "{{ $r->komentar }}"
                            </p>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            @if($r->is_visible)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tampil
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Disembunyikan
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-xs text-gray-500 whitespace-nowrap">
                            {{ $r->created_at ? $r->created_at->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap space-x-2">
                            <form method="POST" action="{{ route('admin.reviews.toggle-visibility', $r->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs px-2.5 py-1.5 rounded-lg border {{ $r->is_visible ? 'border-gray-200 text-gray-600 hover:bg-gray-100' : 'border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }} transition">
                                    {{ $r->is_visible ? 'Sembunyikan' : 'Tampilkan' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.reviews.destroy', $r->id) }}" class="inline" onsubmit="return confirm('Hapus ulasan ini secara permanen?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs px-2.5 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                            <span class="text-3xl block mb-2">💬</span>
                            <p class="font-medium">Belum ada ulasan yang sesuai dengan filter.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $reviews->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
