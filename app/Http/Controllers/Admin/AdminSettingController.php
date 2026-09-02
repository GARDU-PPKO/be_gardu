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
    private const GROUPS = [
        'website' => [
            'title' => 'Website & Integrasi Sistem',
            'desc' => 'Domain frontend web, link Web AR, token gateway WhatsApp Fonnte, dan banner hero.',
            'keys' => [
                'fe_url' => 'URL Frontend Website',
                'ar_url' => 'URL Aplikasi Web AR',
                'fonnte_token' => 'Token API Fonnte',
                'hero_image' => 'Banner / Hero Image',
            ],
        ],
        'sosmed' => [
            'title' => 'Media Sosial',
            'desc' => 'Tautan profil akun media sosial resmi desa.',
            'keys' => [
                'sosmed_ig' => 'Instagram Desa',
                'sosmed_fb' => 'Facebook Desa',
                'sosmed_yt' => 'YouTube Desa',
                'sosmed_tiktok' => 'TikTok Desa',
            ],
        ],
        'payment' => [
            'title' => 'Pembayaran & Rekening',
            'desc' => 'Rekening bank transfer dan barcode QRIS untuk penerimaan transaksi booking wisata.',
            'keys' => [
                'rekening_bank' => 'Nama Bank Transfer',
                'rekening_no' => 'Nomor Rekening Bank',
                'rekening_atas_nama' => 'Atas Nama Pemilik Rekening',
                'qris_image' => 'Barcode QRIS Pembayaran',
            ],
        ],
        'pengelola' => [
            'title' => 'Pengelola & Kebijakan Wisata',
            'desc' => 'Jam operasional check-in/out, jam pelayanan, kebijakan pembatalan, dan batas jam malam.',
            'keys' => [
                'check_in_time' => 'Jam Kedatangan (Check-In)',
                'check_out_time' => 'Jam Kepulangan (Check-Out)',
                'cancel_policy' => 'Kebijakan Pembatalan & Reschedule',
                'night_curfew' => 'Batas Jam Malam',
                'jam_pelayanan' => 'Jam Pelayanan Wisata',
            ],
        ],
        'desa' => [
            'title' => 'Informasi Desa & Kontak Admin',
            'desc' => 'Identitas desa, alamat lengkap, email, dan nomor WhatsApp admin penerima notifikasi booking.',
            'keys' => [
                'nama_desa' => 'Nama Desa Wisata',
                'alamat_desa' => 'Alamat Lengkap Desa',
                'email_desa' => 'Email Resmi Desa',
                'wa_admin' => 'Nomor WhatsApp Admin',
            ],
        ],
    ];

    public function index(Request $request): View
    {
        $allSettings = Setting::all()->keyBy('key');
        $activeTab = $request->get('tab', 'all');

        $groups = self::GROUPS;
        $groupedSettings = [];

        foreach ($groups as $groupKey => $groupInfo) {
            $items = [];
            foreach ($groupInfo['keys'] as $key => $humanLabel) {
                if ($allSettings->has($key)) {
                    $setting = $allSettings->get($key);
                    $setting->human_label = $humanLabel;
                    $items[] = $setting;
                }
            }
            if (! empty($items)) {
                $groupedSettings[$groupKey] = [
                    'key' => $groupKey,
                    'title' => $groupInfo['title'],
                    'desc' => $groupInfo['desc'],
                    'items' => $items,
                ];
            }
        }

        // Cek jika ada setting yang belum masuk ke grup manapun
        $categorizedKeys = collect($groups)->pluck('keys')->map(fn ($k) => array_keys($k))->flatten()->toArray();
        $otherSettings = $allSettings->filter(fn ($s) => ! in_array($s->key, $categorizedKeys, true))->values();
        if ($otherSettings->isNotEmpty()) {
            $groupedSettings['other'] = [
                'key' => 'other',
                'title' => 'Pengaturan Lainnya',
                'desc' => 'Konfigurasi tambahan sistem.',
                'items' => $otherSettings->map(function ($s) {
                    $s->human_label = ucwords(str_replace('_', ' ', $s->key));
                    return $s;
                })->all(),
            ];
        }

        return view('admin.settings.index', [
            'groupedSettings' => $groupedSettings,
            'activeTab' => $activeTab,
            'totalSettings' => $allSettings->count(),
        ]);
    }

    public function edit($id): View
    {
        $setting = Setting::findOrFail($id);
        $humanLabel = null;
        $groupName = null;

        foreach (self::GROUPS as $gKey => $gInfo) {
            if (isset($gInfo['keys'][$setting->key])) {
                $humanLabel = $gInfo['keys'][$setting->key];
                $groupName = $gInfo['title'];
                break;
            }
        }

        $setting->human_label = $humanLabel ?? ucwords(str_replace('_', ' ', $setting->key));
        $setting->group_name = $groupName ?? 'Pengaturan Lainnya';

        return view('admin.settings.edit', ['setting' => $setting]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $setting = Setting::findOrFail($id);
        $data = $request->validate([
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $nullableKeys = ['sosmed_fb', 'sosmed_yt', 'sosmed_ig', 'sosmed_tiktok', 'hero_image'];
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
