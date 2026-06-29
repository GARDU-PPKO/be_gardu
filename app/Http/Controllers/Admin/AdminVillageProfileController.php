<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VillageProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminVillageProfileController extends Controller
{
    public function index(): View
    {
        return view('admin.village-profile.index', ['profiles' => VillageProfile::orderBy('urutan')->get()]);
    }

    public function create(): View
    {
        return view('admin.village-profile.form', ['profile' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tipe' => 'required|in:sejarah,visi,misi,pemerintahan',
            'judul' => 'required|string|max:200',
            'konten' => 'required|string',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['created_by'] = $request->user()->id;
        VillageProfile::create($data);

        return redirect()->route('admin.village-profile.index')->with('success', 'Profil desa berhasil ditambahkan');
    }

    public function edit($id): View
    {
        return view('admin.village-profile.form', ['profile' => VillageProfile::findOrFail($id)]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $profile = VillageProfile::findOrFail($id);
        $data = $request->validate([
            'tipe' => 'required|in:sejarah,visi,misi,pemerintahan',
            'judul' => 'required|string|max:200',
            'konten' => 'required|string',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $profile->update($data);
        return redirect()->route('admin.village-profile.index')->with('success', 'Profil desa berhasil diupdate');
    }

    public function destroy($id): RedirectResponse
    {
        VillageProfile::findOrFail($id)->delete();
        return redirect()->route('admin.village-profile.index')->with('success', 'Profil desa berhasil dihapus');
    }
}
