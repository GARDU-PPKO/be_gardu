<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use App\Models\VillageProfile;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Profil Desa')]
class VillageProfileController extends Controller
{
    use ApiResponse;

    #[Endpoint('Daftar Profil Desa')]
    public function index(): JsonResponse
    {
        $profiles = VillageProfile::where('is_active', true)->orderBy('urutan')->get();
        return $this->success($profiles);
    }
}
