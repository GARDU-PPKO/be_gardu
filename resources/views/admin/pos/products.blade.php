@extends('admin.layouts.app')

@section('title', 'Kelola Produk POS')

@section('content')
<div class="space-y-6 font-sans">
    <!-- Top Action Bar -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2.5 mb-0.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                    <svg width="16" height="16" style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <h2 class="text-lg font-bold text-slate-900">Katalog Produk POS</h2>
            </div>
            <p class="text-xs text-slate-500">Kelola inventaris item jualan kasir (makanan, minuman, souvenir, dan tiket)</p>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <a href="{{ route('admin.pos.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 flex-1 sm:flex-initial">
                <svg width="16" height="16" style="width:16px;height:16px;flex-shrink:0;" class="text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span>Buka Terminal Kasir</span>
            </a>
            <button type="button" onclick="openCreateModal()" class="px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-700/20 transition flex items-center justify-center gap-1.5 flex-1 sm:flex-initial active:scale-[0.98]">
                <svg width="16" height="16" style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Produk Baru</span>
            </button>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.pos.products.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk atau SKU..." class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                <svg width="16" height="16" style="width:16px;height:16px;flex-shrink:0;" class="text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            
            <select name="category_id" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                <option value="">-- Semua Kategori --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition flex-1">Filter</button>
                <a href="{{ route('admin.pos.products.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Product Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="p-4">Produk</th>
                        <th class="p-4">SKU</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4">Harga Unit</th>
                        <th class="p-4 text-center">Stok</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $p)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-slate-100 border border-slate-200/60 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                        @if($p->image)
                                            <img src="{{ $p->image }}" class="w-full h-full object-cover">
                                        @else
                                            <svg width="20" height="20" style="width:20px;height:20px;flex-shrink:0;" class="text-slate-400 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                        @endif
                                    </div>
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $p->name }}</div>
                                </div>
                            </td>
                            <td class="p-4 font-mono text-slate-500">{{ $p->sku ?? '-' }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200/60">
                                    {{ $p->category->name ?? 'Umum' }}
                                </span>
                            </td>
                            <td class="p-4 font-extrabold text-emerald-700 text-sm">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $p->stock > 5 ? 'bg-emerald-100 text-emerald-800' : ($p->stock > 0 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $p->stock }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                @if($p->is_active)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" onclick='openEditModal(@json($p))' class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold rounded-lg transition">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('admin.pos.products.destroy', $p->id) }}" onsubmit="return confirm('Yakin menghapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold rounded-lg transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-slate-400">
                                <p class="text-xs font-semibold">Belum ada produk POS yang tersedia.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/30">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add/Edit Product Modal Dialog -->
<div id="productModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center hidden p-4 animate-in fade-in duration-200">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 border border-slate-100">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900" id="modalTitle">Tambah Produk POS</h3>
            <button type="button" onclick="closeProductModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">✕</button>
        </div>
        
        <form id="productForm" method="POST" action="{{ route('admin.pos.products.store') }}" enctype="multipart/form-data" class="space-y-3 text-xs">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="inputName" required placeholder="Contoh: Kopi Robusta Getas" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kategori</label>
                    <select name="category_id" id="inputCategoryId" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                        <option value="">-- Pilih --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kode SKU</label>
                    <input type="text" name="sku" id="inputSku" placeholder="Otomatis jika kosong" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Harga (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="price" id="inputPrice" min="0" required placeholder="10000" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Stok Awal <span class="text-rose-500">*</span></label>
                    <input type="number" name="stock" id="inputStock" min="0" required placeholder="50" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Foto Produk</label>
                <input type="file" name="image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="inputIsActive" value="1" checked class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                <label for="inputIsActive" class="font-bold text-slate-700">Tampilkan di Terminal Kasir</label>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 mt-4">
                <button type="button" onclick="closeProductModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-700 text-white font-bold hover:bg-emerald-800 transition shadow-sm">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Produk POS';
    document.getElementById('productForm').action = "{{ route('admin.pos.products.store') }}";
    document.getElementById('formMethod').value = 'POST';
    
    document.getElementById('inputName').value = '';
    document.getElementById('inputCategoryId').value = '';
    document.getElementById('inputSku').value = '';
    document.getElementById('inputPrice').value = '';
    document.getElementById('inputStock').value = '10';
    document.getElementById('inputIsActive').checked = true;

    document.getElementById('productModal').classList.remove('hidden');
}

function openEditModal(product) {
    document.getElementById('modalTitle').textContent = 'Edit Produk POS';
    document.getElementById('productForm').action = `/admin/pos/products/${product.id}`;
    document.getElementById('formMethod').value = 'PUT';

    document.getElementById('inputName').value = product.name;
    document.getElementById('inputCategoryId').value = product.category_id || '';
    document.getElementById('inputSku').value = product.sku || '';
    document.getElementById('inputPrice').value = product.price;
    document.getElementById('inputStock').value = product.stock;
    document.getElementById('inputIsActive').checked = Boolean(product.is_active);

    document.getElementById('productModal').classList.remove('hidden');
}

function closeProductModal() {
    document.getElementById('productModal').classList.add('hidden');
}
</script>
@endsection
