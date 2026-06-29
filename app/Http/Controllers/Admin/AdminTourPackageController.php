<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourPackage;
use App\Models\TourPackageInclude;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTourPackageController extends Controller
{
    public function index(): View
    {
        return view('admin.tour-packages.index', ['packages' => TourPackage::with('includes')->get()]);
    }

    public function create(): View
    {
        return view('admin.tour-packages.form', ['package' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => 'required|string|max:200',
            'deskripsi' => 'required|string',
            'harga' => 'required|numeric',
            'satuan' => 'required|in:orang,grup',
            'tag' => 'nullable|string|max:50',
            'durasi' => 'required|string|max:100',
            'min_participants' => 'nullable|integer',
            'max_participants' => 'nullable|integer',
            'gambar' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $data['created_by'] = $request->user()->id;
        TourPackage::create($data);

        return redirect()->route('admin.tour-packages.index')->with('success', 'Paket wisata berhasil ditambahkan');
    }

    public function show($id): View
    {
        return view('admin.tour-packages.show', ['package' => TourPackage::with('includes')->findOrFail($id)]);
    }

    public function edit($id): View
    {
        return view('admin.tour-packages.form', ['package' => TourPackage::with('includes')->findOrFail($id)]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $package = TourPackage::findOrFail($id);
        $data = $request->validate([
            'nama' => 'required|string|max:200',
            'deskripsi' => 'required|string',
            'harga' => 'required|numeric',
            'satuan' => 'required|in:orang,grup',
            'tag' => 'nullable|string|max:50',
            'durasi' => 'required|string|max:100',
            'min_participants' => 'nullable|integer',
            'max_participants' => 'nullable|integer',
            'gambar' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $package->update($data);
        return redirect()->route('admin.tour-packages.index')->with('success', 'Paket wisata berhasil diupdate');
    }

    public function destroy($id): RedirectResponse
    {
        TourPackage::findOrFail($id)->delete();
        return redirect()->route('admin.tour-packages.index')->with('success', 'Paket wisata berhasil dihapus');
    }

    public function storeInclude(Request $request, $id): RedirectResponse
    {
        $package = TourPackage::findOrFail($id);
        $data = $request->validate([
            'item' => 'required|string|max:255',
        ]);

        $package->includes()->create($data);
        return redirect()->route('admin.tour-packages.edit', $id)->with('success', 'Include berhasil ditambahkan');
    }

    public function destroyInclude($id, $includeId): RedirectResponse
    {
        TourPackageInclude::findOrFail($includeId)->delete();
        return redirect()->route('admin.tour-packages.edit', $id)->with('success', 'Include berhasil dihapus');
    }
}
