<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use App\Models\Budaya;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Http\JsonResponse;

#[Group('Budaya')]
class BudayaController extends Controller
{
    use ApiResponse;

    #[Endpoint('Daftar Budaya')]
    public function index(): JsonResponse
    {
        $budaya = Budaya::with('schedules')->where('is_active', true)->get();
        return $this->success($budaya);
    }

    #[Endpoint('Detail Budaya')]
    #[PathParameter('id', description: 'ID budaya (UUID)', example: '6a5b4c3d-2e1f-0a9b-8c7d-6e5f4a3b2c1d')]
    public function show($id): JsonResponse
    {
        $budaya = Budaya::with('schedules')->where('is_active', true)->findOrFail($id);
        return $this->success($budaya);
    }
}
