<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaketWisata;
use App\Models\PaketWisataTier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminPaketWisataController extends Controller
{
    public function index(): View
    {
        return view('admin.paket-wisata.index', [
            'packages' => PaketWisata::with('tiers')->orderBy('nama')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.paket-wisata.form', ['package' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['created_by'] = $request->user()->id;

        PaketWisata::create($data);

        return redirect()->route('admin.paket-wisata.index')->with('success', 'Paket wisata berhasil ditambahkan');
    }

    public function show($id): View
    {
        return view('admin.paket-wisata.show', [
            'package' => PaketWisata::with('tiers')->findOrFail($id),
        ]);
    }

    public function edit($id): View
    {
        return view('admin.paket-wisata.form', [
            'package' => PaketWisata::with('tiers')->findOrFail($id),
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $package = PaketWisata::findOrFail($id);
        if ($request->hasFile('gambar')) {
            $this->deleteOldImage($package->gambar);
        }
        $package->update($this->validatedData($request));

        return redirect()->route('admin.paket-wisata.index')->with('success', 'Paket wisata berhasil diupdate');
    }

    public function destroy($id): RedirectResponse
    {
        PaketWisata::findOrFail($id)->delete();

        return redirect()->route('admin.paket-wisata.index')->with('success', 'Paket wisata diarsipkan (soft delete)');
    }

    public function restore($id): RedirectResponse
    {
        PaketWisata::withTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.paket-wisata.index')->with('success', 'Paket wisata dipulihkan');
    }

    public function storeTier(Request $request, $id): RedirectResponse
    {
        $package = PaketWisata::findOrFail($id);

        abort_unless($package->tipe_harga === 'per_orang_tier', 422);

        $request->validate([
            'min_peserta' => 'required|integer|min:1',
            'harga_per_orang' => 'required|numeric|min:0',
        ]);

        $package->tiers()->create([
            'min_peserta' => $request->min_peserta,
            'harga_per_orang' => $request->harga_per_orang,
        ]);

        return redirect()->route('admin.paket-wisata.edit', $id)->with('success', 'Tier harga berhasil ditambahkan');
    }

    public function destroyTier($id, $tierId): RedirectResponse
    {
        PaketWisataTier::findOrFail($tierId)->delete();

        return redirect()->route('admin.paket-wisata.edit', $id)->with('success', 'Tier harga dihapus');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'kategori' => 'required|string|max:50',
            'tipe_harga' => 'required|in:per_orang_tier,per_paket_fixed',
            'kapasitas_per_unit' => 'nullable|integer|min:1',
            'harga_paket' => 'nullable|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|max:2048',
            'tag' => 'nullable|string|max:50',
            'durasi' => 'nullable|string|max:100',
            'aktif' => 'boolean',
        ]);

        if ($data['tipe_harga'] === 'per_paket_fixed') {
            $data['harga_paket'] = $request->harga_paket;
            $data['kapasitas_per_unit'] = $request->kapasitas_per_unit;
        } else {
            $data['harga_paket'] = null;
            $data['kapasitas_per_unit'] = null;
        }

        $data['fasilitas'] = array_values(array_filter(array_map('trim', explode("\n", (string) $request->input('fasilitas')))));
        $data['aktif'] = $request->boolean('aktif');

        if ($request->hasFile('gambar')) {
            $data['gambar'] = Storage::url($request->file('gambar')->store('paket-wisata', 'public'));
        } else {
            unset($data['gambar']);
        }

        return $data;
    }
}
