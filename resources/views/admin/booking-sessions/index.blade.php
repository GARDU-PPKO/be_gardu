@extends('admin.layouts.app')
@section('title', 'Sesi Booking')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Sesi Booking</h2>
        <a href="{{ route('admin.booking-sessions.create') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">+ Tambah Sesi</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b bg-gray-50">
                    <th class="p-4 font-semibold">Sesi</th>
                    <th class="p-4 font-semibold">Jam Mulai</th>
                    <th class="p-4 font-semibold">Jam Selesai</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="p-4 font-medium">{{ $session->nama }}</td>
                    <td class="p-4">{{ $session->jam_mulai }}</td>
                    <td class="p-4">{{ $session->jam_selesai }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $session->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $session->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="p-4 flex gap-2">
                        <a href="{{ route('admin.booking-sessions.edit', $session->id) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs hover:bg-blue-700 transition">Edit</a>
                        <form method="POST" action="{{ route('admin.booking-sessions.destroy', $session->id) }}" onsubmit="return confirm('Yakin hapus?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs hover:bg-red-700 transition">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-8 text-center text-gray-400">Belum ada sesi booking</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
