@extends('admin.layouts.app')
@section('title', 'Dusun')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Dusun</h2>
        <a href="{{ route('admin.dusun.create') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">+ Tambah Dusun</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b bg-gray-50">
                    <th class="p-4 font-semibold">Nama</th>
                    <th class="p-4 font-semibold">RW</th>
                    <th class="p-4 font-semibold">Jumlah RT</th>
                    <th class="p-4 font-semibold">Penduduk</th>
                    <th class="p-4 font-semibold">Luas Wilayah</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dusunList as $dusun)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="p-4">{{ $dusun->nama }}</td>
                    <td class="p-4">{{ $dusun->rw }}</td>
                    <td class="p-4">{{ $dusun->jumlah_rt }}</td>
                    <td class="p-4">{{ $dusun->jumlah_penduduk }}</td>
                    <td class="p-4">{{ $dusun->luas_wilayah }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $dusun->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $dusun->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="p-4 flex gap-2">
                        <a href="{{ route('admin.dusun.edit', $dusun->id) }}" class="text-blue-600 hover:text-blue-800 text-xs">Edit</a>
                        <form method="POST" action="{{ route('admin.dusun.destroy', $dusun->id) }}" onsubmit="return confirm('Yakin hapus?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="p-8 text-center text-gray-400">Belum ada dusun</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
