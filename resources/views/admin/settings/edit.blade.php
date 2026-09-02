@extends('admin.layouts.app')
@section('title', 'Edit Pengaturan - ' . ($setting->human_label ?? $setting->key))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">{{ $setting->group_name }}</span>
            <h2 class="text-2xl font-bold text-gray-800">{{ $setting->human_label ?? $setting->key }}</h2>
        </div>
        <a href="{{ route('admin.settings.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800 transition">← Kembali ke Pengaturan</a>
    </div>

    <form method="POST" action="{{ route('admin.settings.update', $setting->id) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200/90 p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <label class="text-gray-500 font-medium block mb-1">Key Konfigurasi (Internal)</label>
                    <p class="font-mono text-xs px-3 py-2 bg-gray-50 rounded-lg border border-gray-200 text-gray-600 select-all">{{ $setting->key }}</p>
                </div>
                <div>
                    <label class="text-gray-500 font-medium block mb-1">Grup Menu</label>
                    <p class="text-xs px-3 py-2 bg-emerald-50 text-emerald-800 rounded-lg border border-emerald-100 font-semibold">{{ $setting->group_name }}</p>
                </div>

                @if($setting->key === 'qris_image')
                <div class="sm:col-span-2 space-y-2 pt-2">
                    <label class="text-gray-700 font-semibold block">Gambar Barcode QRIS</label>
                    @if($setting->value)
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 inline-block">
                            <img src="{{ $setting->value }}" alt="QRIS saat ini" class="h-44 w-44 object-contain rounded-lg bg-white p-2 border border-gray-200 shadow-sm">
                            <span class="text-[11px] text-gray-500 block text-center mt-1 font-medium">Gambar Saat Ini</span>
                        </div>
                    @endif
                    <input type="file" name="value" accept="image/*" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                    <p class="text-[11px] text-gray-400">Format: JPG, PNG, WEBP. Maksimal 2MB.</p>
                    @error('value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @else
                <div class="sm:col-span-2 space-y-1.5 pt-2">
                    <label class="text-gray-700 font-semibold block">
                        Nilai Konfigurasi (Value) {{ in_array($setting->key, ['sosmed_fb', 'sosmed_yt', 'sosmed_ig', 'sosmed_tiktok', 'hero_image']) ? '<span class="text-gray-400 font-normal">(Opsional)</span>' : '<span class="text-red-500">*</span>' }}
                    </label>
                    <textarea name="value" rows="3" {{ in_array($setting->key, ['sosmed_fb', 'sosmed_yt', 'sosmed_ig', 'sosmed_tiktok', 'hero_image']) ? '' : 'required' }} class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm font-mono text-gray-800 leading-relaxed">{{ old('value', $setting->value) }}</textarea>
                    @error('value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

                <div class="sm:col-span-2 space-y-1.5">
                    <label class="text-gray-700 font-semibold block">Keterangan / Deskripsi Pengaturan</label>
                    <input type="text" name="deskripsi" value="{{ old('deskripsi', $setting->deskripsi) }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                    @error('deskripsi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-4">
            <button type="submit" class="px-6 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-sm font-semibold transition shadow-sm">
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.settings.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-medium transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
