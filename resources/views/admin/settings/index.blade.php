@extends('admin.layouts.app')
@section('title', 'Pengaturan')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Pengaturan</h2>
    </div>

    <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b bg-gray-50">
                    <th class="p-4 font-semibold">Key</th>
                    <th class="p-4 font-semibold">Value</th>
                    <th class="p-4 font-semibold">Deskripsi</th>
                    <th class="p-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($settings as $setting)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="p-4 font-mono text-xs">{{ $setting->key }}</td>
                    <td class="p-4 max-w-xs truncate">{{ $setting->value }}</td>
                    <td class="p-4">{{ $setting->deskripsi ?? '-' }}</td>
                    <td class="p-4">
                        <a href="{{ route('admin.settings.edit', $setting->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 hover:text-blue-700 transition-colors" title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="p-8 text-center text-gray-400">Belum ada pengaturan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(isset($editSetting))
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm p-6 space-y-4">
        <h3 class="text-lg font-bold text-gray-800">Edit Pengaturan</h3>
        <form method="POST" action="{{ route('admin.settings.update', $editSetting->id) }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Key</label>
                <input type="text" value="{{ $editSetting->key }}" disabled class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-gray-50 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Value *</label>
                <textarea name="value" rows="3" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">{{ old('value', $editSetting->value) }}</textarea>
                @error('value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <input type="text" name="deskripsi" value="{{ old('deskripsi', $editSetting->deskripsi) }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('deskripsi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2 bg-[#0d3b2e] text-white hover:bg-[#092b21] rounded-lg text-sm hover:bg-emerald-800 transition">Simpan</button>
                <a href="{{ route('admin.settings.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">Batal</a>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
