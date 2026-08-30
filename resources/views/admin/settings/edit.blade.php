@extends('admin.layouts.app')
@section('title', 'Edit Pengaturan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Edit Pengaturan</h2>
        <a href="{{ route('admin.settings.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.settings.update', $setting->id) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Key</span>
                    <p class="font-mono mt-1 px-3 py-2 bg-gray-50 rounded-lg border border-gray-200 text-gray-500">{{ $setting->key }}</p>
                </div>
                <div></div>
                @if($setting->key === 'qris_image')
                <div class="col-span-2">
                    <span class="text-gray-500 block mb-1">Value (Foto QRIS)</span>
                    @if($setting->value)
                        <img src="{{ $setting->value }}" alt="QRIS saat ini" class="h-40 w-40 object-contain rounded-lg border border-gray-300 mb-2 bg-white">
                    @endif
                    <input type="file" name="value" accept="image/*" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    @error('value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @else
                <div class="col-span-2">
                    <span class="text-gray-500 block mb-1">Value {{ in_array($setting->key, ['sosmed_fb', 'sosmed_yt', 'sosmed_web', 'sosmed_ig', 'sosmed_tiktok', 'hero_image']) ? '(Opsional)' : '*' }}</span>
                    <textarea name="value" rows="3" {{ in_array($setting->key, ['sosmed_fb', 'sosmed_yt', 'sosmed_web', 'sosmed_ig', 'sosmed_tiktok', 'hero_image']) ? '' : 'required' }} class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">{{ old('value', $setting->value) }}</textarea>
                    @error('value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

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
