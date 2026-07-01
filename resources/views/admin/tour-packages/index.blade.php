@extends('admin.layouts.app')
@section('title', 'Paket Wisata')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Paket Wisata</h2>
        <a href="{{ route('admin.tour-packages.create') }}" class="px-4 py-2 bg-[#0d3b2e] text-white hover:bg-[#092b21] rounded-lg text-sm hover:bg-emerald-800 transition">+ Tambah Paket</a>
    </div>

    <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden">
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
                    <td class="p-4 flex items-center gap-1.5">
                        <a href="{{ route('admin.tour-packages.edit', $package->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 hover:text-blue-700 transition-colors" title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.tour-packages.destroy', $package->id) }}" onsubmit="return confirm('Yakin hapus?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700 transition-colors" title="Hapus">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
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
