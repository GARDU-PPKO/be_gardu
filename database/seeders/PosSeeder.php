<?php

namespace Database\Seeders;

use App\Models\PosCategory;
use App\Models\PosProduct;
use Illuminate\Database\Seeder;

class PosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Tiket & Sewa', 'slug' => 'tiket-sewa'],
            ['name' => 'Makanan & Snack', 'slug' => 'makanan-snack'],
            ['name' => 'Minuman', 'slug' => 'minuman'],
            ['name' => 'Souvenir & Kerajinan', 'slug' => 'souvenir-kerajinan'],
        ];

        foreach ($categories as $cat) {
            PosCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        $tiketCat = PosCategory::where('slug', 'tiket-sewa')->first();
        $makananCat = PosCategory::where('slug', 'makanan-snack')->first();
        $minumanCat = PosCategory::where('slug', 'minuman')->first();
        $souvenirCat = PosCategory::where('slug', 'souvenir-kerajinan')->first();

        $products = [
            // Tiket & Sewa
            [
                'name' => 'Tiket Tubing Adventure (Sungai Blukar)',
                'sku' => 'TKT-001',
                'category_id' => $tiketCat->id ?? null,
                'price' => 75000,
                'stock' => 100,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Sewa ATV Adventure (30 Menit)',
                'sku' => 'TKT-002',
                'category_id' => $tiketCat->id ?? null,
                'price' => 35000,
                'stock' => 15,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1578637387939-43c525550085?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Sewa Tenda Camping Dome (Kap. 4)',
                'sku' => 'TKT-003',
                'category_id' => $tiketCat->id ?? null,
                'price' => 50000,
                'stock' => 20,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Paket Bakaran BBQ + Alat & Arang',
                'sku' => 'TKT-004',
                'category_id' => $tiketCat->id ?? null,
                'price' => 25000,
                'stock' => 30,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Tiket Kolam Renang & Wahana Air',
                'sku' => 'TKT-005',
                'category_id' => $tiketCat->id ?? null,
                'price' => 15000,
                'stock' => 200,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1576013551627-0cc20b96c2a7?auto=format&fit=crop&w=600&q=80',
            ],

            // Makanan & Snack
            [
                'name' => 'Nasi Goreng Kampung Spesial Getas',
                'sku' => 'FOOD-001',
                'category_id' => $makananCat->id ?? null,
                'price' => 18000,
                'stock' => 50,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Ubi Goreng Crispy Saus Gula Aren',
                'sku' => 'FOOD-002',
                'category_id' => $makananCat->id ?? null,
                'price' => 10000,
                'stock' => 60,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1528751014936-863e6e7a319c?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Pisang Goreng Keju Coklat Lumer',
                'sku' => 'FOOD-003',
                'category_id' => $makananCat->id ?? null,
                'price' => 12000,
                'stock' => 45,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1587314168485-3236d6710814?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Mie Goreng Jawa Telur Bebek',
                'sku' => 'FOOD-004',
                'category_id' => $makananCat->id ?? null,
                'price' => 15000,
                'stock' => 40,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Keripik Singkong Pedas Manis',
                'sku' => 'FOOD-005',
                'category_id' => $makananCat->id ?? null,
                'price' => 12000,
                'stock' => 80,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?auto=format&fit=crop&w=600&q=80',
            ],

            // Minuman
            [
                'name' => 'Kopi Robusta Getas Asli',
                'sku' => 'DRINK-001',
                'category_id' => $minumanCat->id ?? null,
                'price' => 10000,
                'stock' => 80,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Es Kelapa Muda Fresh Gula Aren',
                'sku' => 'DRINK-002',
                'category_id' => $minumanCat->id ?? null,
                'price' => 12000,
                'stock' => 50,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Wedang Jahe Rempah Tradisional',
                'sku' => 'DRINK-003',
                'category_id' => $minumanCat->id ?? null,
                'price' => 8000,
                'stock' => 60,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Es Teh Manis Jumbo Fresh',
                'sku' => 'DRINK-004',
                'category_id' => $minumanCat->id ?? null,
                'price' => 5000,
                'stock' => 150,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=600&q=80',
            ],

            // Souvenir
            [
                'name' => 'Kaos Wisata Getas Premium',
                'sku' => 'SOV-001',
                'category_id' => $souvenirCat->id ?? null,
                'price' => 65000,
                'stock' => 30,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Kopi Arabika Getas 200g',
                'sku' => 'SOV-002',
                'category_id' => $souvenirCat->id ?? null,
                'price' => 45000,
                'stock' => 40,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Gantungan Kunci Kayu Ukir',
                'sku' => 'SOV-003',
                'category_id' => $souvenirCat->id ?? null,
                'price' => 8000,
                'stock' => 100,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Madu Murni Hutan Getas (250ml)',
                'sku' => 'SOV-004',
                'category_id' => $souvenirCat->id ?? null,
                'price' => 85000,
                'stock' => 20,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1587049352847-4a222e784d38?auto=format&fit=crop&w=600&q=80',
            ],
        ];

        foreach ($products as $prod) {
            PosProduct::updateOrCreate(['sku' => $prod['sku']], $prod);
        }
    }
}
