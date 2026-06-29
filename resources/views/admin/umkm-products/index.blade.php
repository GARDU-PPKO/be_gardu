@extends('admin.layouts.app')
@section('title', 'Produk UMKM')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Produk UMKM</h2>
        <a href="{{ route('admin.umkm-products.create') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">+ Tambah Produk</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b bg-gray-50">
                    <th class="p-4 font-semibold">Nama</th>
                    <th class="p-4 font-semibold">Kategori</th>
                    <th class="p-4 font-semibold">Harga</th>
                    <th class="p-4 font-semibold">No. WA</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="p-4">{{ $product->nama }}</td>
                    <td class="p-4">{{ $product->kategori }}</td>
                    <td class="p-4">Rp {{ number_format($product->harga, 0, ',', '.') }}</td>
                    <td class="p-4 font-mono text-xs">{{ $product->no_wa_penjual }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="p-4 flex gap-2">
                        <a href="{{ route('admin.umkm-products.edit', $product->id) }}" class="text-blue-600 hover:text-blue-800 text-xs">Edit</a>
                        <form method="POST" action="{{ route('admin.umkm-products.destroy', $product->id) }}" onsubmit="return confirm('Yakin hapus?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-8 text-center text-gray-400">Belum ada produk UMKM</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
