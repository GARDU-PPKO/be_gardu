@extends('admin.layouts.app')
@section('title', $budaya ? 'Edit Budaya' : 'Tambah Budaya')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">{{ $budaya ? 'Edit Budaya' : 'Tambah Budaya' }}</h2>
        <a href="{{ route('admin.budaya.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
    </div>

    <form method="POST" action="{{ $budaya ? route('admin.budaya.update', $budaya->id) : route('admin.budaya.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf
        @if($budaya) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul *</label>
                <input type="text" name="judul" value="{{ old('judul', $budaya->judul ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('judul') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label>
                <input type="text" name="kategori" value="{{ old('kategori', $budaya->kategori ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('kategori') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gambar URL *</label>
                <input type="text" name="gambar" value="{{ old('gambar', $budaya->gambar ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('gambar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Span Grid</label>
                <input type="number" name="span_grid" value="{{ old('span_grid', $budaya->span_grid ?? '') }}" min="1" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('span_grid') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $budaya->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">Aktif</label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi *</label>
            <textarea name="deskripsi" rows="5" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">{{ old('deskripsi', $budaya->deskripsi ?? '') }}</textarea>
            @error('deskripsi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Simpan</button>
            <a href="{{ route('admin.budaya.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>

    @if($budaya)
    <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        <h3 class="font-bold text-gray-800">Jadwal Acara</h3>

        @if($budaya->schedules->count() > 0)
        <div class="space-y-2">
            @foreach($budaya->schedules as $schedule)
            <div class="flex items-center justify-between text-sm border-b border-gray-100 pb-2">
                <div>
                    <span class="font-medium">{{ $schedule->nama_acara }}</span>
                    <span class="text-gray-500">({{ $schedule->hari }}, {{ $schedule->jam }})</span>
                </div>
                <form method="POST" action="{{ route('admin.budaya.schedules.destroy', [$budaya->id, $schedule->id]) }}" onsubmit="return confirm('Yakin hapus?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                </form>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-gray-400 text-sm">Belum ada jadwal</p>
        @endif

        <form method="POST" action="{{ route('admin.budaya.schedules.store', $budaya->id) }}" class="grid grid-cols-4 gap-2">
            @csrf
            <input type="text" name="nama_acara" placeholder="Nama Acara" required class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            <input type="text" name="hari" placeholder="Hari (Senin)" required class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            <input type="text" name="jam" placeholder="Jam (10:00)" required class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            <button type="submit" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Tambah</button>
        </form>
    </div>
    @endif
</div>
@endsection
