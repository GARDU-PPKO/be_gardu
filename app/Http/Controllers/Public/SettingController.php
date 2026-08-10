<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\ApiResponse;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Pengaturan')]
class SettingController extends Controller
{
    #[Endpoint('Daftar Pengaturan')]
    #[QueryParameter('keys', description: 'Filter berdasarkan key (dipisah koma)', required: false, example: 'nama_desa,alamat_desa,rekening_bank')]
    public function index(Request $request): JsonResponse
    {
        $query = Setting::query()->orderBy('id');

        if ($request->filled('keys')) {
            $keys = explode(',', $request->string('keys')->toString());
            $query->whereIn('key', array_map('trim', $keys));
        }

        $settings = $query->get()->map(fn (Setting $s) => [
            'id' => $s->id,
            'key' => $s->key,
            'value' => $s->value,
            'deskripsi' => $s->deskripsi,
        ])->values();

        return ApiResponse::success($settings, 'Success retrieving settings');
    }
}
