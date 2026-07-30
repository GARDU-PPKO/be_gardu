<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use App\Models\UmkmProduct;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Produk UMKM')]
class UmkmProductController extends Controller
{
    use ApiResponse;

    #[Endpoint('Daftar Produk UMKM')]
    #[QueryParameter('kategori', description: 'Filter berdasarkan kategori produk', required: false, example: 'Makanan')]
    public function index(Request $request): JsonResponse
    {
        $query = UmkmProduct::where('is_active', true);

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        return $this->success($query->get());
    }
}
