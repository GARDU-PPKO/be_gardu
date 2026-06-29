@extends('admin.layouts.app')
@section('title', $profile ? 'Edit Profil Desa' : 'Tambah Profil Desa')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">{{ $profile ? 'Edit Profil Desa' : 'Tambah Profil Desa' }}</h2>
        <a href="{{ route('admin.village-profile.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
    </div>

    <form method="POST" action="{{ $profile ? route('admin.village-profile.update', $profile->id) : route('admin.village-profile.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf
        @if($profile) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe *</label>
            <select name="tipe" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                <option value="sejarah" {{ old('tipe', $profile->tipe ?? '') === 'sejarah' ? 'selected' : '' }}>Sejarah</option>
                <option value="visi" {{ old('tipe', $profile->tipe ?? '') === 'visi' ? 'selected' : '' }}>Visi</option>
                <option value="misi" {{ old('tipe', $profile->tipe ?? '') === 'misi' ? 'selected' : '' }}>Misi</option>
                <option value="pemerintahan" {{ old('tipe', $profile->tipe ?? '') === 'pemerintahan' ? 'selected' : '' }}>Pemerintahan</option>
            </select>
            @error('tipe') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul *</label>
            <input type="text" name="judul" value="{{ old('judul', $profile->judul ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            @error('judul') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Konten *</label>
            <textarea name="konten" rows="8" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">{{ old('konten', $profile->konten ?? '') }}</textarea>
            @error('konten') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                <input type="number" name="urutan" value="{{ old('urutan', $profile->urutan ?? '') }}" min="0" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('urutan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $profile->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">Aktif</label>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Simpan</button>
            <a href="{{ route('admin.village-profile.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
