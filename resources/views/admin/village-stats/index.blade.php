@extends('admin.layouts.app')
@section('title', 'Statistik Desa')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Statistik Desa</h2>
        <a href="{{ route('admin.village-stats.create') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">+ Tambah Statistik</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b bg-gray-50">
                    <th class="p-4 font-semibold">Label</th>
                    <th class="p-4 font-semibold">Nilai</th>
                    <th class="p-4 font-semibold">Satuan</th>
                    <th class="p-4 font-semibold">Icon</th>
                    <th class="p-4 font-semibold">Urutan</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stats as $stat)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="p-4">{{ $stat->label }}</td>
                    <td class="p-4 font-semibold">{{ $stat->nilai }}</td>
                    <td class="p-4">{{ $stat->satuan ?? '-' }}</td>
                    <td class="p-4">{{ $stat->icon ?? '-' }}</td>
                    <td class="p-4">{{ $stat->urutan ?? '-' }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $stat->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $stat->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="p-4 flex gap-2">
                        <a href="{{ route('admin.village-stats.edit', $stat->id) }}" class="text-blue-600 hover:text-blue-800 text-xs">Edit</a>
                        <form method="POST" action="{{ route('admin.village-stats.destroy', $stat->id) }}" onsubmit="return confirm('Yakin hapus?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="p-8 text-center text-gray-400">Belum ada statistik</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
