<?php

namespace App\Providers;

use App\Models\PaketWisata;
use App\Models\PosProduct;
use App\Models\UmkmProduct;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Relation::morphMap([
            'paket_wisata' => PaketWisata::class,
            'umkm_product' => UmkmProduct::class,
            'pos_product' => PosProduct::class,
        ]);
    }
}
