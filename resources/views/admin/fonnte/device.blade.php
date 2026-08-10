@extends('admin.layouts.app')
@section('title', 'Fonnte Device')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Status Fonnte</h2>
        <a href="{{ route('admin.fonnte.device') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Refresh</a>
    </div>

    @if(isset($device['status']) && $device['status'] === 'skipped')
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-6 rounded-xl">
        <p class="font-semibold">Token belum dikonfigurasi</p>
        <p class="text-sm mt-1">Atur <code class="bg-yellow-100 px-1 rounded">FONNTE_TOKEN</code> di file .env.</p>
    </div>
    @elseif(isset($device['status']) && $device['status'] === 'error')
    <div class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-xl">
        <p class="font-semibold">Gagal ambil data device</p>
        <p class="text-sm mt-1">{{ $device['message'] ?? 'Coba refresh atau periksa token.' }}</p>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @foreach($device as $key => $val)
        @if(is_array($val))
        <div class="border-b pb-3">
            <span class="text-gray-500 text-sm font-medium block mb-1">{{ $key }}</span>
            <div class="ml-2 space-y-1">
                @foreach($val as $k => $v)
                <div class="flex gap-2 text-sm">
                    <span class="text-gray-400 w-32">{{ $k }}:</span>
                    <span class="font-medium">{{ is_array($v) ? json_encode($v) : $v }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="flex gap-2 border-b pb-3">
            <span class="text-gray-500 text-sm w-48">{{ $key }}:</span>
            <span class="font-medium text-sm">{{ $val }}</span>
        </div>
        @endif
        @endforeach
    </div>
    @endif
</div>
@endsection
