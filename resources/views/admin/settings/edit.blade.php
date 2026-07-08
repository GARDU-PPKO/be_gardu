@extends('admin.layouts.app')
@section('title', 'Edit Pengaturan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Edit Pengaturan</h2>
        <a href="{{ route('admin.settings.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.settings.update', $setting->id) }}">
        @csrf @method('PUT')
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Key</span>
                    <p class="font-mono mt-1 px-3 py-2 bg-gray-50 rounded-lg border border-gray-200 text-gray-500">{{ $setting->key }}</p>
                </div>
                <div></div>
                <div class="col-span-2">
                    <span class="text-gray-500 block mb-1">Value *</span>
                    <textarea name="value" rows="3" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">{{ old('value', $setting->value) }}</textarea>
                    @error('value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-2">
                    <span class="text-gray-500 block mb-1">Deskripsi</span>
                    <input type="text" name="deskripsi" value="{{ old('deskripsi', $setting->deskripsi) }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                    @error('deskripsi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition font-semibold">Simpan Perubahan</button>
            <a href="{{ route('admin.settings.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
