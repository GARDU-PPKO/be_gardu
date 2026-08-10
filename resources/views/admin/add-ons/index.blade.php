@extends('admin.layouts.app')
@section('title', 'Add-On')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Add-On</h2>
        <a href="{{ route('admin.add-ons.create') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">+ Tambah Add-On</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b bg-gray-50">
                    <th class="p-4 font-semibold">Nama</th>
                    <th class="p-4 font-semibold">Kategori</th>
                    <th class="p-4 font-semibold">Model Harga</th>
                    <th class="p-4 font-semibold">Harga</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($addOns as $addOn)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="p-4 font-medium">{{ $addOn->nama }}</td>
                    <td class="p-4 capitalize">{{ $addOn->kategori ?? '-' }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $addOn->tipe_harga === 'per_orang' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                            {{ $addOn->tipe_harga === 'per_orang' ? 'Per Orang' : 'Per Unit' }}
                        </span>
                    </td>
                    <td class="p-4">Rp {{ number_format($addOn->harga, 0, ',', '.') }} <span class="text-xs text-gray-400">/{{ $addOn->tipe_harga === 'per_orang' ? 'orang' : 'unit' }}</span></td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $addOn->aktif ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $addOn->aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="p-4 flex gap-2">
                        <a href="{{ route('admin.add-ons.edit', $addOn->id) }}" class="text-blue-600 hover:text-blue-800 text-xs">Edit</a>
                        @if($addOn->trashed())
                        <form method="POST" action="{{ route('admin.add-ons.restore', $addOn->id) }}">
                            @csrf
                            <button type="submit" class="text-emerald-600 hover:text-emerald-800 text-xs">Restore</button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('admin.add-ons.destroy', $addOn->id) }}" onsubmit="return confirm('Yakin hapus add-on ini? (soft delete, bisa dipulihkan)')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-8 text-center text-gray-400">Belum ada add-on</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t">
            {{ $addOns->links() }}
        </div>
    </div>
</div>
@endsection
