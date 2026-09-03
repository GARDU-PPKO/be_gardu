@extends('admin.layouts.app')
@section('title', 'Add-On')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h2 class="text-2xl font-bold text-gray-800">Add-On</h2>
        <a href="{{ route('admin.add-ons.create') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">+ Tambah Add-On</a>
    </div>

    <!-- Tab Filter & Pencarian -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.add-ons.index', array_merge(request()->except('tab', 'page'), ['tab' => 'active'])) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $tab === 'active' ? 'bg-emerald-700 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                Aktif ({{ $totalActive }})
            </a>
            <a href="{{ route('admin.add-ons.index', array_merge(request()->except('tab', 'page'), ['tab' => 'trashed'])) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $tab === 'trashed' ? 'bg-red-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                Sampah ({{ $totalTrashed }})
            </a>
            <a href="{{ route('admin.add-ons.index', array_merge(request()->except('tab', 'page'), ['tab' => 'all'])) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $tab === 'all' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                Semua ({{ $totalAll }})
            </a>
        </div>

        <div class="flex items-center gap-2">
            @if($tab === 'trashed' && $totalTrashed > 0 && Route::has('admin.add-ons.trash.empty'))
                <form method="POST" action="{{ route('admin.add-ons.trash.empty') }}" onsubmit="return confirm('Kosongkan semua sampah? {{ $totalTrashed }} add-on ini akan dihapus permanen.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-medium transition">
                        Kosongkan Sampah ({{ $totalTrashed }})
                    </button>
                </form>
            @endif

            <form method="GET" action="{{ route('admin.add-ons.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama add-on..." class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none w-48 sm:w-56 bg-white">
                <button type="submit" class="px-3 py-1.5 bg-gray-800 text-white rounded-lg text-xs hover:bg-gray-900 transition">Cari</button>
                @if(request('search'))
                    <a href="{{ route('admin.add-ons.index', ['tab' => $tab]) }}" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-xs hover:bg-gray-300 transition">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Tabel Data Add-On -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b bg-gray-50 text-xs">
                        <th class="p-4 font-semibold">Nama</th>
                        <th class="p-4 font-semibold">Kategori</th>
                        <th class="p-4 font-semibold">Model Harga</th>
                        <th class="p-4 font-semibold">Harga</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($addOns as $addOn)
                    <tr class="hover:bg-gray-50 transition {{ $addOn->trashed() ? 'bg-red-50/20' : '' }}">
                        <td class="p-4">
                            <div class="font-medium text-gray-900">{{ $addOn->nama }}</div>
                            @if($addOn->deskripsi)
                                <div class="text-xs text-gray-400 line-clamp-1 mt-0.5">{{ $addOn->deskripsi }}</div>
                            @endif
                        </td>
                        <td class="p-4 capitalize text-gray-600">{{ $addOn->kategori ?: '-' }}</td>
                        <td class="p-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $addOn->tipe_harga === 'per_orang' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ $addOn->tipe_harga === 'per_orang' ? 'Per Orang' : 'Per Unit' }}
                            </span>
                        </td>
                        <td class="p-4 font-medium text-gray-900">
                            Rp {{ number_format($addOn->harga, 0, ',', '.') }}
                            <span class="text-xs text-gray-400 font-normal">/{{ $addOn->labelSatuan() }}</span>
                        </td>
                        <td class="p-4">
                            @if($addOn->trashed())
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600 font-medium">
                                    Terhapus
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full {{ $addOn->aktif ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $addOn->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($addOn->trashed())
                                    <form method="POST" action="{{ route('admin.add-ons.restore', $addOn->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs transition">
                                            Restore
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.add-ons.destroy', $addOn->id) }}" onsubmit="return confirm('Hapus permanen add-on \'{{ $addOn->nama }}\'?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs transition">
                                            Hapus Permanen
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.add-ons.edit', $addOn->id) }}" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs transition">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.add-ons.destroy', $addOn->id) }}" onsubmit="return confirm('Yakin hapus permanen add-on \'{{ $addOn->nama }}\'?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs transition">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400">
                            {{ $tab === 'trashed' ? 'Tempat sampah kosong' : 'Belum ada add-on' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($addOns->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $addOns->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
