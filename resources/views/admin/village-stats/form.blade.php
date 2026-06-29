@extends('admin.layouts.app')
@section('title', $stat ? 'Edit Statistik' : 'Tambah Statistik')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">{{ $stat ? 'Edit Statistik' : 'Tambah Statistik' }}</h2>
        <a href="{{ route('admin.village-stats.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
    </div>

    <form method="POST" action="{{ $stat ? route('admin.village-stats.update', $stat->id) : route('admin.village-stats.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf
        @if($stat) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Label *</label>
                <input type="text" name="label" value="{{ old('label', $stat->label ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('label') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nilai *</label>
                <input type="text" name="nilai" value="{{ old('nilai', $stat->nilai ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('nilai') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                <input type="text" name="satuan" value="{{ old('satuan', $stat->satuan ?? '') }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('satuan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                <input type="text" name="icon" value="{{ old('icon', $stat->icon ?? '') }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('icon') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                <input type="number" name="urutan" value="{{ old('urutan', $stat->urutan ?? '') }}" min="0" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('urutan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $stat->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">Aktif</label>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Simpan</button>
            <a href="{{ route('admin.village-stats.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
