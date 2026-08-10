@extends('admin.layouts.app')
@section('title', 'Pengaturan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Pengaturan</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
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
                        <a href="{{ route('admin.settings.edit', $setting->id) }}" class="px-3 py-1.5 bg-emerald-700 text-white rounded-lg text-xs hover:bg-emerald-800 transition font-medium inline-block">Edit</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="p-8 text-center text-gray-400">Belum ada pengaturan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
