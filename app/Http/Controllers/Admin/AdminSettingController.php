<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', ['settings' => Setting::all()]);
    }

    public function edit($id): View
    {
        return view('admin.settings.edit', ['setting' => Setting::findOrFail($id)]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $setting = Setting::findOrFail($id);
        $data = $request->validate([
            'value' => 'required|string',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $setting->update($data);
        return redirect()->route('admin.settings.index')->with('success', 'Pengaturan berhasil diupdate');
    }
}
