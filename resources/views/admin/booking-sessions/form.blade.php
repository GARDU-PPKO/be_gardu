@extends('admin.layouts.app')
@section('title', $session ? 'Edit Sesi Booking' : 'Tambah Sesi Booking')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">{{ $session ? 'Edit Sesi Booking' : 'Tambah Sesi Booking' }}</h2>
        <a href="{{ route('admin.booking-sessions.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
    </div>

    <form method="POST" action="{{ $session ? route('admin.booking-sessions.update', $session->id) : route('admin.booking-sessions.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf
        @if($session) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Paket Wisata *</label>
            <select name="package_id" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                <option value="">-- Pilih Paket --</option>
                @foreach($packages as $pkg)
                <option value="{{ $pkg->id }}" {{ old('package_id', $session->package_id ?? '') == $pkg->id ? 'selected' : '' }}>{{ $pkg->nama }}</option>
                @endforeach
            </select>
            @error('package_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
            <input type="date" name="tanggal" value="{{ old('tanggal', $session->tanggal ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            @error('tanggal') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sesi *</label>
            <select name="sesi" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                <option value="Pagi" {{ old('sesi', $session->sesi ?? '') === 'Pagi' ? 'selected' : '' }}>Pagi</option>
                <option value="Siang" {{ old('sesi', $session->sesi ?? '') === 'Siang' ? 'selected' : '' }}>Siang</option>
                <option value="Sore" {{ old('sesi', $session->sesi ?? '') === 'Sore' ? 'selected' : '' }}>Sore</option>
            </select>
            @error('sesi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kuota *</label>
                <input type="number" name="kuota" value="{{ old('kuota', $session->kuota ?? '') }}" required min="1" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('kuota') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Terisi</label>
                <input type="number" name="terisi" value="{{ old('terisi', $session->terisi ?? 0) }}" min="0" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('terisi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $session->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <label for="is_active" class="text-sm font-medium text-gray-700">Aktif</label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Simpan</button>
            <a href="{{ route('admin.booking-sessions.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
