<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Models\DusunGallery;
use App\Models\DusunKeunggulan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDusunController extends Controller
{
    public function index(): View
    {
        return view('admin.dusun.index', ['dusunList' => Dusun::with(['galleries', 'keunggulan'])->get()]);
    }

    public function create(): View
    {
        return view('admin.dusun.form', ['dusun' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'rw' => 'required|string|max:10',
            'jumlah_rt' => 'required|integer',
            'jumlah_penduduk' => 'required|integer',
            'luas_wilayah' => 'required|string|max:50',
            'deskripsi' => 'required|string',
            'detail' => 'required|string',
            'hero_img' => 'required|string|max:255',
            'thumbnail' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $data['created_by'] = $request->user()->id;
        $dusun = Dusun::create($data);

        return redirect()->route('admin.dusun.index')->with('success', 'Dusun berhasil ditambahkan');
    }

    public function show($id): View
    {
        return view('admin.dusun.show', ['dusun' => Dusun::with(['galleries', 'keunggulan'])->findOrFail($id)]);
    }

    public function edit($id): View
    {
        return view('admin.dusun.form', ['dusun' => Dusun::with(['galleries', 'keunggulan'])->findOrFail($id)]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $dusun = Dusun::findOrFail($id);
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'rw' => 'required|string|max:10',
            'jumlah_rt' => 'required|integer',
            'jumlah_penduduk' => 'required|integer',
            'luas_wilayah' => 'required|string|max:50',
            'deskripsi' => 'required|string',
            'detail' => 'required|string',
            'hero_img' => 'required|string|max:255',
            'thumbnail' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $dusun->update($data);
        return redirect()->route('admin.dusun.index')->with('success', 'Dusun berhasil diupdate');
    }

    public function destroy($id): RedirectResponse
    {
        Dusun::findOrFail($id)->delete();
        return redirect()->route('admin.dusun.index')->with('success', 'Dusun berhasil dihapus');
    }

    public function storeGallery(Request $request, $id): RedirectResponse
    {
        $dusun = Dusun::findOrFail($id);
        $data = $request->validate([
            'image_url' => 'required|string|max:255',
        ]);

        $dusun->galleries()->create($data);
        return redirect()->route('admin.dusun.edit', $id)->with('success', 'Galeri berhasil ditambahkan');
    }

    public function destroyGallery($id, $galleryId): RedirectResponse
    {
        DusunGallery::findOrFail($galleryId)->delete();
        return redirect()->route('admin.dusun.edit', $id)->with('success', 'Galeri berhasil dihapus');
    }

    public function storeKeunggulan(Request $request, $id): RedirectResponse
    {
        $dusun = Dusun::findOrFail($id);
        $data = $request->validate([
            'keunggulan' => 'required|string|max:255',
        ]);

        $dusun->keunggulan()->create($data);
        return redirect()->route('admin.dusun.edit', $id)->with('success', 'Keunggulan berhasil ditambahkan');
    }

    public function destroyKeunggulan($id, $keunggulanId): RedirectResponse
    {
        DusunKeunggulan::findOrFail($keunggulanId)->delete();
        return redirect()->route('admin.dusun.edit', $id)->with('success', 'Keunggulan berhasil dihapus');
    }
}
