@extends('admin.layouts.app')
@section('title', 'Sesi Booking')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Template Sesi Booking</h2>
            <p class="text-sm text-gray-500 mt-1">Master sesi acuan per paket — slot terisi dihitung otomatis dari data booking</p>
        </div>
        <a href="{{ route('admin.booking-sessions.create') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">+ Tambah Template</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b bg-gray-50">
                    <th class="p-4 font-semibold">Paket</th>
                    <th class="p-4 font-semibold">Sesi</th>
                    <th class="p-4 font-semibold">Jam Operasional</th>
                    <th class="p-4 font-semibold">Kuota</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="p-4 font-medium">{{ $session->package->nama ?? '-' }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs rounded-full
                            {{ $session->sesi === 'Pagi' ? 'bg-orange-100 text-orange-700' : '' }}
                            {{ $session->sesi === 'Siang' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $session->sesi === 'Sore' ? 'bg-blue-100 text-blue-700' : '' }}">
                            {{ $session->sesi }}
                        </span>
                    </td>
                    <td class="p-4 text-gray-600">
                        @if($session->jam_mulai && $session->jam_selesai)
                            {{ \Carbon\Carbon::parse($session->jam_mulai)->format('H:i') }} – {{ \Carbon\Carbon::parse($session->jam_selesai)->format('H:i') }} WIB
                        @else
                            <span class="text-gray-400 italic">Belum diatur</span>
                        @endif
                    </td>
                    <td class="p-4">{{ $session->kuota }} orang</td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $session->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $session->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="p-4 flex gap-2">
                        <a href="{{ route('admin.booking-sessions.edit', $session->id) }}" class="text-blue-600 hover:text-blue-800 text-xs">Edit</a>
                        <form method="POST" action="{{ route('admin.booking-sessions.destroy', $session->id) }}" onsubmit="return confirm('Yakin hapus template ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-8 text-center text-gray-400">Belum ada template sesi booking</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t">
            {{ $sessions->links() }}
        </div>
    </div>
</div>
@endsection

