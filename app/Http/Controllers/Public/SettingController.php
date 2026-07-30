<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use App\Models\Setting;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Pengaturan')]
class SettingController extends Controller
{
    use ApiResponse;

    #[Endpoint('Daftar Pengaturan')]
    #[QueryParameter('keys', description: 'Filter berdasarkan key (dipisah koma)', required: false, example: 'nama_desa,alamat_desa,rekening_bank')]
    public function index(Request $request): JsonResponse
    {
        $query = Setting::query();

        if ($request->filled('keys')) {
            $keys = explode(',', $request->keys);
            $query->whereIn('key', $keys);
        }

        return $this->success($query->get());
    }
}
