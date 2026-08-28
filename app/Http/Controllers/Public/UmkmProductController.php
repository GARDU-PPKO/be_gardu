<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\UmkmProduct;
use App\Support\ApiResponse;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Produk UMKM')]
class UmkmProductController extends Controller
{
    #[Endpoint('Daftar Produk UMKM')]
    #[QueryParameter('kategori', description: 'Filter berdasarkan kategori produk', required: false, example: 'Makanan')]
    public function index(Request $request): JsonResponse
    {
        $query = UmkmProduct::where('is_active', true);

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $limit = (int) $request->integer('limit', 15);
        $limit = max(1, min(100, $limit));

        $paginator = $query->orderBy('id')->paginate($limit)->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(fn (UmkmProduct $p) => [
                'id' => $p->id,
                'nama' => $p->nama,
                'kategori' => $p->kategori,
                'harga' => (float) $p->harga,
                'deskripsi' => $p->deskripsi,
                'gambar' => $p->gambar,
                'no_wa_penjual' => $p->no_wa_penjual,
                'is_active' => (bool) $p->is_active,
            ])
        );

        return ApiResponse::paginated($paginator, 'Success retrieving UMKM products');
    }
}
