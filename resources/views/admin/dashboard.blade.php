@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-emerald-500">
            <p class="text-gray-500 text-sm">Total Dusun</p>
            <p class="text-3xl font-bold text-gray-800">{{ $total_dusun }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-gray-500 text-sm">Paket Wisata</p>
            <p class="text-3xl font-bold text-gray-800">{{ $total_packages }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-amber-500">
            <p class="text-gray-500 text-sm">Total Booking</p>
            <p class="text-3xl font-bold text-gray-800">{{ $total_bookings }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-red-500">
            <p class="text-gray-500 text-sm">Booking Pending</p>
            <p class="text-3xl font-bold text-gray-800">{{ $pending_bookings }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
            <p class="text-gray-500 text-sm">UMKM</p>
            <p class="text-3xl font-bold text-gray-800">{{ $total_umkm }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-pink-500">
            <p class="text-gray-500 text-sm">Budaya</p>
            <p class="text-3xl font-bold text-gray-800">{{ $total_budaya }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-bold text-gray-800 mb-4">Booking Terbaru</h3>
        @if($recent_bookings->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-3 font-semibold">Kode</th>
                        <th class="pb-3 font-semibold">Pemesan</th>
                        <th class="pb-3 font-semibold">Paket</th>
                        <th class="pb-3 font-semibold">Tanggal</th>
                        <th class="pb-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent_bookings as $booking)
                    <tr class="border-b border-gray-100">
                        <td class="py-3 font-mono text-xs">{{ $booking->kode_booking }}</td>
                        <td class="py-3">{{ $booking->nama_pemesan }}</td>
                        <td class="py-3">{{ $booking->package->nama ?? '-' }}</td>
                        <td class="py-3">{{ $booking->tanggal }}</td>
                        <td class="py-3">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($booking->status === 'confirmed') bg-green-100 text-green-700
                                @elseif($booking->status === 'cancelled') bg-red-100 text-red-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ $booking->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-400">Belum ada booking</p>
        @endif
    </div>
</div>
@endsection
