<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminAddOnController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'active');
        $query = AddOn::query();

        if ($tab === 'trashed') {
            $query->onlyTrashed();
        } elseif ($tab === 'all') {
            $query->withTrashed();
        } else {
            // default 'active': non-trashed items
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('tipe_harga')) {
            $query->where('tipe_harga', $request->tipe_harga);
        }

        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->where('aktif', true);
            } elseif ($request->status === 'nonaktif') {
                $query->where('aktif', false);
            }
        }

        $addOns = $query->orderBy('urutan')->orderBy('nama')->paginate(15)->withQueryString();

        $totalActive = AddOn::count();
        $totalTrashed = AddOn::onlyTrashed()->count();
        $totalAll = AddOn::withTrashed()->count();
        $categories = AddOn::withTrashed()->whereNotNull('kategori')->where('kategori', '!=', '')->distinct()->pluck('kategori');

        return view('admin.add-ons.index', compact(
            'addOns',
            'tab',
            'totalActive',
            'totalTrashed',
            'totalAll',
            'categories'
        ));
    }

    public function emptyTrash(): RedirectResponse
    {
        $trashed = AddOn::onlyTrashed()->get();
        $count = $trashed->count();
        foreach ($trashed as $addOn) {
            if ($addOn->gambar) {
                $this->deleteOldImage($addOn->gambar);
            }
            $addOn->forceDelete();
        }

        return redirect()->route('admin.add-ons.index', ['tab' => 'trashed'])->with('success', "{$count} add-on di tempat sampah berhasil dihapus permanen");
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
        if ($request->hasFile('gambar')) {
            $this->deleteOldImage($addOn->gambar);
        }
        $addOn->update($this->validatedData($request));

        return redirect()->route('admin.add-ons.index')->with('success', 'Add-on berhasil diupdate');
    }

    public function destroy($id): RedirectResponse
    {
        $addOn = AddOn::withTrashed()->findOrFail($id);
        if ($addOn->gambar) {
            $this->deleteOldImage($addOn->gambar);
        }
        $addOn->forceDelete();

        return redirect()->route('admin.add-ons.index')->with('success', 'Add-on berhasil dihapus permanen');
    }

    public function forceDelete($id): RedirectResponse
    {
        $addOn = AddOn::withTrashed()->findOrFail($id);
        if ($addOn->gambar) {
            $this->deleteOldImage($addOn->gambar);
        }
        $addOn->forceDelete();

        return redirect()->route('admin.add-ons.index')->with('success', 'Add-on berhasil dihapus permanen');
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
            'gambar' => 'nullable|image|max:2048',
            'aktif' => 'boolean',
            'urutan' => 'nullable|integer|min:1',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        if ($request->hasFile('gambar')) {
            $data['gambar'] = Storage::url($request->file('gambar')->store('add-ons', 'public'));
        } else {
            unset($data['gambar']);
        }

        return $data;
    }
}
