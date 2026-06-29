<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VillageStat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminVillageStatsController extends Controller
{
    public function index(): View
    {
        return view('admin.village-stats.index', ['stats' => VillageStat::orderBy('urutan')->get()]);
    }

    public function create(): View
    {
        return view('admin.village-stats.form', ['stat' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label' => 'required|string|max:200',
            'nilai' => 'required|string|max:50',
            'satuan' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:50',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        VillageStat::create($data);
        return redirect()->route('admin.village-stats.index')->with('success', 'Statistik berhasil ditambahkan');
    }

    public function edit($id): View
    {
        return view('admin.village-stats.form', ['stat' => VillageStat::findOrFail($id)]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $stat = VillageStat::findOrFail($id);
        $data = $request->validate([
            'label' => 'required|string|max:200',
            'nilai' => 'required|string|max:50',
            'satuan' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:50',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $stat->update($data);
        return redirect()->route('admin.village-stats.index')->with('success', 'Statistik berhasil diupdate');
    }

    public function destroy($id): RedirectResponse
    {
        VillageStat::findOrFail($id)->delete();
        return redirect()->route('admin.village-stats.index')->with('success', 'Statistik berhasil dihapus');
    }
}
