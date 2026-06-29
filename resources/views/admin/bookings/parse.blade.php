@extends('admin.layouts.app')
@section('title', 'Parse Text WA')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Parse Text WhatsApp</h2>

    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-xl text-sm">
        <strong>Cara penggunaan:</strong>
        <ol class="list-decimal list-inside mt-2 space-y-1">
            <li>Buka chat WhatsApp dari user yang sudah transfer</li>
            <li>Copy teks pemesanan dari user (format WA template)</li>
            <li>Paste di kolom bawah</li>
            <li>Upload bukti bayar (screenshot dari WA user)</li>
            <li>Klik "Parse & Simpan"</li>
        </ol>
    </div>

    <form method="POST" action="{{ route('admin.bookings.parse-text') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Paste Teks WhatsApp User</label>
            <textarea name="raw_text" rows="10" placeholder="Tempel teks dari chat WhatsApp user di sini..." class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm font-mono">{{ old('raw_text') }}</textarea>
            @error('raw_text') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">URL Bukti Bayar (opsional)</label>
            <input type="text" name="bukti_bayar" value="{{ old('bukti_bayar') }}" placeholder="https://example.com/bukti.jpg" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
        </div>

        @if(session('parsed_data'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm">
            <h4 class="font-bold text-green-800 mb-2">✅ Data berhasil diparse:</h4>
            @foreach(session('parsed_data') as $key => $val)
            <div class="flex gap-2"><span class="text-green-700 w-32">{{ $key }}:</span><span class="font-medium">{{ $val }}</span></div>
            @endforeach
        </div>
        @endif

        @if(session('parse_error'))
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl text-sm">
            {{ session('parse_error') }}
        </div>
        @endif

        <button type="submit" class="px-6 py-3 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition font-semibold">
            Parse & Simpan Booking
        </button>
    </form>
</div>
@endsection
