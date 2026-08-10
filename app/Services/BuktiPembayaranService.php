<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class BuktiPembayaranService
{
    /**
     * Simpan file bukti pembayaran ke storage lokal.
     * - Image: dikompresi (max 1000px, WebP quality 75), nama UUID.
     * - PDF: disimpan apa adanya.
     *
     * @return string path relatif terhadap disk 'local'
     */
    public function simpan(UploadedFile $file): string
    {
        $uuid = (string) Str::uuid();
        $folder = 'bukti-pembayaran/' . now()->format('Y/m');

        if ($file->getClientOriginalExtension() === 'pdf') {
            $filename = "{$uuid}.pdf";
            Storage::disk('local')->putFileAs($folder, $file, $filename);

            return "{$folder}/{$filename}";
        }

        $filename = "{$uuid}.webp";
        $encoded = Image::decode($file)
            ->scaleDown(width: 1000)
            ->encodeUsingMediaType('image/webp', quality: 75);

        Storage::disk('local')->put("{$folder}/{$filename}", (string) $encoded);

        return "{$folder}/{$filename}";
    }
}
