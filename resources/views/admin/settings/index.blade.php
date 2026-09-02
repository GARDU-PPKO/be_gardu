@extends('admin.layouts.app')
@section('title', 'Pengaturan')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Pengaturan Sistem</h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Konfigurasi integrasi website, metode pembayaran, operasional wisata, dan identitas desa.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                Total: {{ $totalSettings }} Item
            </span>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="flex flex-wrap gap-1.5 p-1 bg-gray-100/90 rounded-lg border border-gray-200">
        <a href="{{ route('admin.settings.index', ['tab' => 'all']) }}"
           class="px-3.5 py-1.5 rounded-md text-xs font-medium transition {{ $activeTab === 'all' ? 'bg-white text-gray-900 font-semibold shadow-sm border border-gray-200/80' : 'text-gray-600 hover:text-gray-900 hover:bg-white/40' }}">
            Semua Grup
        </a>
        @foreach($groupedSettings as $gKey => $group)
        <a href="{{ route('admin.settings.index', ['tab' => $gKey]) }}"
           class="px-3.5 py-1.5 rounded-md text-xs font-medium transition flex items-center gap-1.5 {{ $activeTab === $gKey ? 'bg-white text-gray-900 font-semibold shadow-sm border border-gray-200/80' : 'text-gray-600 hover:text-gray-900 hover:bg-white/40' }}">
            <span>{{ $group['title'] }}</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded font-semibold {{ $activeTab === $gKey ? 'bg-gray-100 text-gray-800' : 'bg-gray-200/70 text-gray-600' }}">
                {{ count($group['items']) }}
            </span>
        </a>
        @endforeach
    </div>

    <!-- Grouped Cards -->
    <div class="space-y-5">
        @foreach($groupedSettings as $gKey => $group)
            @if($activeTab === 'all' || $activeTab === $gKey)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Group Header -->
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">{{ $group['title'] }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $group['desc'] }}</p>
                    </div>
                    <span class="text-xs font-medium text-gray-500 bg-white border border-gray-200 px-2.5 py-0.5 rounded self-start sm:self-auto">
                        {{ count($group['items']) }} Konfigurasi
                    </span>
                </div>

                <!-- Group Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100 bg-white">
                                <th class="py-3 px-5 w-1/4">Nama Pengaturan</th>
                                <th class="py-3 px-5 w-2/5">Nilai Saat Ini (Value)</th>
                                <th class="py-3 px-5 w-1/4">Keterangan</th>
                                <th class="py-3 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($group['items'] as $setting)
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <td class="py-3.5 px-5 align-top">
                                    <div class="font-semibold text-gray-900">{{ $setting->human_label ?? $setting->key }}</div>
                                    <span class="text-[11px] font-mono text-gray-400 block mt-0.5">{{ $setting->key }}</span>
                                </td>
                                <td class="py-3.5 px-5 align-top">
                                    @if($setting->key === 'qris_image')
                                        @if($setting->value)
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $setting->value }}" alt="QRIS" class="w-12 h-12 object-contain rounded border border-gray-200 bg-white p-1">
                                                <div>
                                                    <span class="text-xs font-semibold text-gray-800 block">QRIS Tersedia</span>
                                                    <a href="{{ $setting->value }}" target="_blank" class="text-[11px] text-blue-600 hover:underline">Lihat Gambar</a>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum diunggah</span>
                                        @endif
                                    @elseif($setting->key === 'fonnte_token')
                                        <div class="font-mono text-xs text-gray-700 bg-gray-100 px-2 py-0.5 rounded inline-block max-w-xs truncate border border-gray-200">
                                            {{ substr($setting->value, 0, 6) . '••••••••••••' . substr($setting->value, -4) }}
                                        </div>
                                    @elseif(str_starts_with($setting->value ?? '', 'http://') || str_starts_with($setting->value ?? '', 'https://'))
                                        <a href="{{ $setting->value }}" target="_blank" class="text-xs font-mono text-blue-600 hover:text-blue-800 hover:underline max-w-xs truncate block" title="{{ $setting->value }}">
                                            {{ $setting->value }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-800 whitespace-pre-line">{{ $setting->value ?: '-' }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 align-top text-xs text-gray-500">
                                    {{ $setting->deskripsi ?? '-' }}
                                </td>
                                <td class="py-3.5 px-5 align-top text-right">
                                    <a href="{{ route('admin.settings.edit', $setting->id) }}" 
                                       class="px-3 py-1 bg-emerald-700 hover:bg-emerald-800 text-white rounded text-xs font-medium transition shadow-sm inline-block">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        @endforeach
    </div>
</div>
@endsection
