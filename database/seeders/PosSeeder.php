<?php

namespace Database\Seeders;

use App\Models\PosCategory;
use App\Models\PosProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan & Snack', 'slug' => 'makanan-snack'],
            ['name' => 'Minuman', 'slug' => 'minuman'],
            ['name' => 'Souvenir & Kerajinan', 'slug' => 'souvenir-kerajinan'],
            ['name' => 'Tiket & Sewa', 'slug' => 'tiket-sewa'],
        ];

        foreach ($categories as $cat) {
            PosCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        $makananCat = PosCategory::where('slug', 'makanan-snack')->first();
        $minumanCat = PosCategory::where('slug', 'minuman')->first();
        $souvenirCat = PosCategory::where('slug', 'souvenir-kerajinan')->first();
        $tiketCat = PosCategory::where('slug', 'tiket-sewa')->first();

        $products = [
            // Makanan
            [
                'name' => 'Ubi Goreng Getas',
                'sku' => 'FOOD-001',
                'category_id' => $makananCat->id ?? null,
                'price' => 10000,
                'stock' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Paket Snack Lokal',
                'sku' => 'FOOD-002',
                'category_id' => $makananCat->id ?? null,
                'price' => 15000,
                'stock' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Keripik Singkong Khas',
                'sku' => 'FOOD-003',
                'category_id' => $makananCat->id ?? null,
                'price' => 12000,
                'stock' => 40,
                'is_active' => true,
            ],

            // Minuman
            [
                'name' => 'Kopi Robusta Getas',
                'sku' => 'DRINK-001',
                'category_id' => $minumanCat->id ?? null,
                'price' => 10000,
                'stock' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Teh Hangat Manis',
                'sku' => 'DRINK-002',
                'category_id' => $minumanCat->id ?? null,
                'price' => 5000,
                'stock' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Es Kelapa Muda Fresh',
                'sku' => 'DRINK-003',
                'category_id' => $minumanCat->id ?? null,
                'price' => 12000,
                'stock' => 25,
                'is_active' => true,
            ],

            // Souvenir
            [
                'name' => 'Kaos Wisata Getas',
                'sku' => 'SOV-001',
                'category_id' => $souvenirCat->id ?? null,
                'price' => 65000,
                'stock' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Gantungan Kunci Kayu Ukir',
                'sku' => 'SOV-002',
                'category_id' => $souvenirCat->id ?? null,
                'price' => 8000,
                'stock' => 50,
                'is_active' => true,
            ],

            // Tiket & Sewa
            [
                'name' => 'Sewa ATV (30 Menit)',
                'sku' => 'TKT-001',
                'category_id' => $tiketCat->id ?? null,
                'price' => 35000,
                'stock' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Alat Bakaran BBQ + Arang',
                'sku' => 'TKT-002',
                'category_id' => $tiketCat->id ?? null,
                'price' => 25000,
                'stock' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Tiket Masuk Wahana Tubing Extra',
                'sku' => 'TKT-003',
                'category_id' => $tiketCat->id ?? null,
                'price' => 75000,
                'stock' => 100,
                'is_active' => true,
            ],
        ];

        foreach ($products as $prod) {
            PosProduct::firstOrCreate(['sku' => $prod['sku']], $prod);
        }
    }
}
