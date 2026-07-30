<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use App\Models\TourPackage;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Http\JsonResponse;

#[Group('Paket Wisata')]
class TourPackageController extends Controller
{
    use ApiResponse;

    #[Endpoint('Daftar Paket Wisata')]
    public function index(): JsonResponse
    {
        $packages = TourPackage::with('includes')->where('is_active', true)->get();
        return $this->success($packages);
    }

    #[Endpoint('Detail Paket Wisata')]
    #[PathParameter('id', description: 'ID paket wisata (UUID)', example: '1a2b3c4d-5e6f-7a8b-9c0d-1e2f3a4b5c6d')]
    public function show($id): JsonResponse
    {
        $package = TourPackage::with('includes')->where('is_active', true)->findOrFail($id);
        return $this->success($package);
    }
}
