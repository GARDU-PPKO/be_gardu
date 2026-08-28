@extends('admin.layouts.app')
@section('title', $session ? 'Edit Template Sesi' : 'Tambah Template Sesi')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $session ? 'Edit Template Sesi' : 'Tambah Template Sesi' }}</h2>
            <p class="text-sm text-gray-500 mt-1">Template berlaku untuk semua tanggal — slot terisi dihitung otomatis</p>
        </div>
        <a href="{{ route('admin.booking-sessions.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
    </div>

    <form method="POST" action="{{ $session ? route('admin.booking-sessions.update', $session->id) : route('admin.booking-sessions.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf
        @if($session) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sesi *</label>
            <select name="sesi" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                <option value="Pagi"  {{ old('sesi', $session->sesi ?? '') === 'Pagi'  ? 'selected' : '' }}>🌅 Pagi</option>
                <option value="Siang" {{ old('sesi', $session->sesi ?? '') === 'Siang' ? 'selected' : '' }}>☀️ Siang</option>
                <option value="Sore"  {{ old('sesi', $session->sesi ?? '') === 'Sore'  ? 'selected' : '' }}>🌇 Sore</option>
            </select>
            @error('sesi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai</label>
                <input type="time" name="jam_mulai" value="{{ old('jam_mulai', $session->jam_mulai ?? '') }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('jam_mulai') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai</label>
                <input type="time" name="jam_selesai" value="{{ old('jam_selesai', $session->jam_selesai ?? '') }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('jam_selesai') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kuota Maksimal *</label>
            <input type="number" name="kuota" value="{{ old('kuota', $session->kuota ?? '') }}" required min="1" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm" placeholder="Jumlah peserta maksimal">
            @error('kuota') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $session->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <label for="is_active" class="text-sm font-medium text-gray-700">Template Aktif</label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Simpan</button>
            <a href="{{ route('admin.booking-sessions.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
