@extends('admin.layouts.app')
@section('title', 'Paket Wisata')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Paket Wisata</h2>
        <a href="{{ route('admin.tour-packages.create') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">+ Tambah Paket</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b bg-gray-50">
                    <th class="p-4 font-semibold">Nama</th>
                    <th class="p-4 font-semibold">Harga</th>
                    <th class="p-4 font-semibold">Satuan</th>
                    <th class="p-4 font-semibold">Durasi</th>
                    <th class="p-4 font-semibold">Tag</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packages as $package)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="p-4">{{ $package->nama }}</td>
                    <td class="p-4">Rp {{ number_format($package->harga, 0, ',', '.') }}</td>
                    <td class="p-4">{{ $package->satuan }}</td>
                    <td class="p-4">{{ $package->durasi }}</td>
                    <td class="p-4">{{ $package->tag ?? '-' }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $package->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $package->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="p-4 flex gap-2">
                        <a href="{{ route('admin.tour-packages.show', $package->id) }}" class="px-3 py-1.5 bg-gray-600 text-white rounded-lg text-xs hover:bg-gray-700 transition">Detail</a>
                        <a href="{{ route('admin.tour-packages.edit', $package->id) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs hover:bg-blue-700 transition">Edit</a>
                        <form method="POST" action="{{ route('admin.tour-packages.destroy', $package->id) }}" onsubmit="return confirm('Yakin hapus?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs hover:bg-red-700 transition">Hapus</button>
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
@endsection
