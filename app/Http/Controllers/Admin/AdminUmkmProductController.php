<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UmkmProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUmkmProductController extends Controller
{
    public function index(): View
    {
        return view('admin.umkm-products.index', ['products' => UmkmProduct::paginate(25)]);
    }

    public function create(): View
    {
        return view('admin.umkm-products.form', ['product' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => 'required|string|max:200',
            'kategori' => 'required|in:Makanan,Kerajinan,Pertanian,Oleh-Oleh',
            'harga' => 'required|numeric',
            'deskripsi' => 'required|string',
            'gambar' => 'required|string|max:255',
            'no_wa_penjual' => 'required|string|max:20',
            'is_active' => 'boolean',
        ]);

        $data['created_by'] = $request->user()->id;
        UmkmProduct::create($data);

        return redirect()->route('admin.umkm-products.index')->with('success', 'Produk UMKM berhasil ditambahkan');
    }

    public function edit($id): View
    {
        return view('admin.umkm-products.form', ['product' => UmkmProduct::findOrFail($id)]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $product = UmkmProduct::findOrFail($id);
        $data = $request->validate([
            'nama' => 'required|string|max:200',
            'kategori' => 'required|in:Makanan,Kerajinan,Pertanian,Oleh-Oleh',
            'harga' => 'required|numeric',
            'deskripsi' => 'required|string',
            'gambar' => 'required|string|max:255',
            'no_wa_penjual' => 'required|string|max:20',
            'is_active' => 'boolean',
        ]);

        $product->update($data);
        return redirect()->route('admin.umkm-products.index')->with('success', 'Produk UMKM berhasil diupdate');
    }

    public function destroy($id): RedirectResponse
    {
        UmkmProduct::findOrFail($id)->delete();
        return redirect()->route('admin.umkm-products.index')->with('success', 'Produk UMKM berhasil dihapus');
    }
}
