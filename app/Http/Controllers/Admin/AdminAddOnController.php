<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAddOnController extends Controller
{
    public function index(): View
    {
        return view('admin.add-ons.index', [
            'addOns' => AddOn::withTrashed()->orderBy('nama')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.add-ons.form', ['addOn' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['created_by'] = $request->user()->id;

        AddOn::create($data);

        return redirect()->route('admin.add-ons.index')->with('success', 'Add-on berhasil ditambahkan');
    }

    public function edit($id): View
    {
        return view('admin.add-ons.form', ['addOn' => AddOn::withTrashed()->findOrFail($id)]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $addOn = AddOn::withTrashed()->findOrFail($id);
        $addOn->update($this->validatedData($request));

        return redirect()->route('admin.add-ons.index')->with('success', 'Add-on berhasil diupdate');
    }

    public function destroy($id): RedirectResponse
    {
        AddOn::findOrFail($id)->delete();

        return redirect()->route('admin.add-ons.index')->with('success', 'Add-on diarsipkan (soft delete)');
    }

    public function restore($id): RedirectResponse
    {
        AddOn::withTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.add-ons.index')->with('success', 'Add-on dipulihkan');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'kategori' => 'nullable|string|max:50',
            'tipe_harga' => 'required|in:per_orang,per_unit',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|string|max:255',
            'aktif' => 'boolean',
            'urutan' => 'nullable|integer|min:1',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        return $data;
    }
}
