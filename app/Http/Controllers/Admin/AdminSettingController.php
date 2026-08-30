<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $nullableKeys = ['sosmed_fb', 'sosmed_yt', 'sosmed_web', 'sosmed_ig', 'sosmed_tiktok', 'hero_image'];
        $isNullable = in_array($setting->key, $nullableKeys);

        if ($setting->key === 'qris_image') {
            if ($request->hasFile('value')) {
                $request->validate(['value' => 'image|max:2048']);
                $this->deleteOldImage($setting->value);
                $data['value'] = Storage::url($request->file('value')->store('settings', 'public'));
            } elseif ($request->filled('value')) {
                $data['value'] = $request->input('value');
            }
        } elseif ($isNullable) {
            $request->validate(['value' => 'nullable|string|max:500']);
            $data['value'] = $request->input('value') ?? '';
        } else {
            $request->validate(['value' => 'required|string']);
            $data['value'] = $request->input('value');
        }

        $setting->update($data);
        return redirect()->route('admin.settings.index')->with('success', 'Pengaturan berhasil diupdate');
    }

}
