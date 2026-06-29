<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Budaya;
use App\Models\BudayaSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBudayaController extends Controller
{
    public function index(): View
    {
        return view('admin.budaya.index', ['budayaList' => Budaya::with('schedules')->get()]);
    }

    public function create(): View
    {
        return view('admin.budaya.form', ['budaya' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'judul' => 'required|string|max:200',
            'kategori' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'gambar' => 'required|string|max:255',
            'span_grid' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $data['created_by'] = $request->user()->id;
        Budaya::create($data);

        return redirect()->route('admin.budaya.index')->with('success', 'Budaya berhasil ditambahkan');
    }

    public function edit($id): View
    {
        return view('admin.budaya.form', ['budaya' => Budaya::with('schedules')->findOrFail($id)]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $budaya = Budaya::findOrFail($id);
        $data = $request->validate([
            'judul' => 'required|string|max:200',
            'kategori' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'gambar' => 'required|string|max:255',
            'span_grid' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $budaya->update($data);
        return redirect()->route('admin.budaya.index')->with('success', 'Budaya berhasil diupdate');
    }

    public function destroy($id): RedirectResponse
    {
        Budaya::findOrFail($id)->delete();
        return redirect()->route('admin.budaya.index')->with('success', 'Budaya berhasil dihapus');
    }

    public function storeSchedule(Request $request, $id): RedirectResponse
    {
        $budaya = Budaya::findOrFail($id);
        $data = $request->validate([
            'nama_acara' => 'required|string|max:200',
            'hari' => 'required|string|max:50',
            'jam' => 'required|string|max:10',
        ]);

        $budaya->schedules()->create($data);
        return redirect()->route('admin.budaya.edit', $id)->with('success', 'Jadwal berhasil ditambahkan');
    }

    public function destroySchedule($id, $scheduleId): RedirectResponse
    {
        BudayaSchedule::findOrFail($scheduleId)->delete();
        return redirect()->route('admin.budaya.edit', $id)->with('success', 'Jadwal berhasil dihapus');
    }
}
