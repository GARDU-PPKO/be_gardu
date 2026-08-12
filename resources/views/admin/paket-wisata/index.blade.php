@extends('admin.layouts.app')
@section('title', 'Paket Wisata')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h2 class="text-2xl font-bold text-gray-800">Paket Wisata</h2>
        <a href="{{ route('admin.paket-wisata.create') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">+ Tambah Paket</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b bg-gray-50">
                    <th class="p-4 font-semibold">Nama</th>
                    <th class="p-4 font-semibold">Kategori</th>
                    <th class="p-4 font-semibold">Model Harga</th>
                    <th class="p-4 font-semibold">Harga</th>
                    <th class="p-4 font-semibold">Tag</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packages as $package)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="p-4 font-medium">{{ $package->nama }}</td>
                    <td class="p-4 capitalize">{{ $package->kategori }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $package->tipe_harga === 'per_orang_tier' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                            {{ $package->tipe_harga === 'per_orang_tier' ? 'Per Orang (Tier)' : 'Per Paket (Fixed)' }}
                        </span>
                    </td>
                    <td class="p-4">
                        @if($package->tipe_harga === 'per_orang_tier')
                            <span class="text-xs text-gray-500">{{ $package->tiers->count() }} tier</span>
                        @else
                            Rp {{ number_format($package->harga_paket, 0, ',', '.') }}
                            @if($package->kapasitas_per_unit) <span class="text-xs text-gray-400">/{{ $package->kapasitas_per_unit }} org</span> @endif
                        @endif
                    </td>
                    <td class="p-4">{{ $package->tag ?? '-' }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $package->aktif ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $package->aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="p-4 flex gap-2">
                        <a href="{{ route('admin.paket-wisata.show', $package->id) }}" class="text-blue-600 hover:text-blue-800 text-xs">Detail</a>
                        <a href="{{ route('admin.paket-wisata.edit', $package->id) }}" class="text-blue-600 hover:text-blue-800 text-xs">Edit</a>
                        <form method="POST" action="{{ route('admin.paket-wisata.destroy', $package->id) }}" onsubmit="return confirm('Yakin hapus paket ini? (soft delete, bisa dipulihkan)')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="p-8 text-center text-gray-400">Belum ada paket wisata</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
