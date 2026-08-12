<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    /**
     * Hapus file gambar lama dari storage hanya jika itu file lokal
     * milik aplikasi (nilai diawali /storage/). URL eksternal dibiarkan.
     */
    protected function deleteOldImage(?string $path): void
    {
        if ($path && str_starts_with($path, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $path));
        }
    }
}
