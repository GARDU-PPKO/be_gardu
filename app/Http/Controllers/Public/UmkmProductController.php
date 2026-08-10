<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\UmkmProduct;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UmkmProductController extends Controller
{
    /**
     * Ambil daftar produk UMKM desa (dengan pagination).
     *
     * @queryParam kategori string|null Filter kategori (Makanan, Kerajinan, Pertanian, Oleh-Oleh).
     * @queryParam page int Halaman saat ini. Example: 1
     * @queryParam limit int Jumlah item per halaman. Example: 15
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "200",
     *     "message": "Success retrieving UMKM products"
     *   },
     *   "data": [
     *     {
     *       "id": 1,
     *       "nama": "Kopi Arabika Getas",
     *       "kategori": "Oleh-Oleh",
     *       "harga": 65000,
     *       "deskripsi": "Kopi asli buatan petani lokal Getas (200g)",
     *       "gambar": "https://...",
     *       "no_wa_penjual": "62812345007",
     *       "is_active": true
     *     }
     *   ],
     *   "pagination": {
     *     "current_page": 1,
     *     "per_page": 15,
     *     "total": 1,
     *     "last_page": 1,
     *     "from": 1,
     *     "to": 1
     *   }
     * }
     */
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
