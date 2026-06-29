<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Booking;
use App\Models\BookingSession;
use App\Models\Budaya;
use App\Models\BudayaSchedule;
use App\Models\Dusun;
use App\Models\DusunGallery;
use App\Models\DusunKeunggulan;
use App\Models\Setting;
use App\Models\TourPackage;
use App\Models\TourPackageInclude;
use App\Models\UmkmProduct;
use App\Models\VillageProfile;
use App\Models\VillageStat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::create([
            'username' => 'superadmin',
            'name' => 'Super Admin',
            'email' => 'superadmin@getas.desa',
            'password' => Hash::make('superadmin123'),
            'nama' => 'Super Admin',
            'role' => 'superadmin',
        ]);

        $admin = User::create([
            'username' => 'admin',
            'name' => 'Admin Desa Getas',
            'email' => 'admin@getas.desa',
            'password' => Hash::make('admin123'),
            'nama' => 'Admin Desa Getas',
            'role' => 'admin',
        ]);

        // Dusun
        $dusunData = [
            ['nama' => 'Seklotok', 'rw' => 'RW 01', 'jumlah_rt' => 3, 'jumlah_penduduk' => 412, 'luas_wilayah' => '1,2 km²', 'deskripsi' => 'Dusun di tepi sungai dengan sawah hijau membentang luas.', 'detail' => 'Seklotok adalah dusun yang berbatasan langsung dengan aliran Sungai Blukar. Hamparan sawah organik membentang hijau sepanjang musim tanam. Warganya dikenal sebagai petani padi terbaik di Desa Getas.', 'hero_img' => 'https://images.unsplash.com/photo-1627796863235-2dddce3e862d?w=800&h=500&fit=crop&auto=format', 'thumbnail' => 'https://images.unsplash.com/photo-1627796863235-2dddce3e862d?w=400&h=300&fit=crop&auto=format', 'keunggulan' => ['Sawah organik tepi sungai', 'Pemandangan matahari terbit terbaik', 'Akses jalur tubing utama'], 'galeri' => ['https://images.unsplash.com/photo-1627796863235-2dddce3e862d?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1683506684881-efbb5203eacf?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1546845776-dcdf70fd09e3?w=600&h=400&fit=crop&auto=format']],
            ['nama' => 'Mambang', 'rw' => 'RW 02', 'jumlah_rt' => 3, 'jumlah_penduduk' => 445, 'luas_wilayah' => '1,4 km²', 'deskripsi' => 'Kawasan pertanian organik unggulan Desa Getas.', 'detail' => 'Mambang dikenal sebagai lumbung pangan Desa Getas. Sistem pertanian organik diterapkan secara konsisten sejak 2015. Produk beras organiknya telah merambah pasar Kabupaten Kendal.', 'hero_img' => 'https://images.unsplash.com/photo-1683506684881-efbb5203eacf?w=800&h=500&fit=crop&auto=format', 'thumbnail' => 'https://images.unsplash.com/photo-1683506684881-efbb5203eacf?w=400&h=300&fit=crop&auto=format', 'keunggulan' => ['Sentra beras organik', 'Kelompok tani aktif', 'Irigasi teknis terbaik'], 'galeri' => ['https://images.unsplash.com/photo-1683506684881-efbb5203eacf?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1536304929831-ee1ca9d44906?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1627796863235-2dddce3e862d?w=600&h=400&fit=crop&auto=format']],
            ['nama' => 'Jolinggo', 'rw' => 'RW 03', 'jumlah_rt' => 2, 'jumlah_penduduk' => 387, 'luas_wilayah' => '1,6 km²', 'deskripsi' => 'Dikelilingi hutan pinus dengan udara sejuk sepanjang hari.', 'detail' => 'Jolinggo terletak di ketinggian paling tinggi di Desa Getas, dikelilingi hutan pinus dan tanaman kopi. Udaranya paling sejuk dan cocok untuk agrowisata perkebunan.', 'hero_img' => 'https://images.unsplash.com/photo-1672128558402-8e03471c8779?w=800&h=500&fit=crop&auto=format', 'thumbnail' => 'https://images.unsplash.com/photo-1672128558402-8e03471c8779?w=400&h=300&fit=crop&auto=format', 'keunggulan' => ['Hutan pinus dan kopi', 'Agrowisata perkebunan', 'Udara pegunungan segar'], 'galeri' => ['https://images.unsplash.com/photo-1672128558402-8e03471c8779?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1637993921206-cae1c2cbba20?w=600&h=400&fit=crop&auto=format']],
            ['nama' => 'Genting', 'rw' => 'RW 04', 'jumlah_rt' => 2, 'jumlah_penduduk' => 356, 'luas_wilayah' => '1,1 km²', 'deskripsi' => 'Dusun yang terkenal dengan kerajinan bambu tradisional.', 'detail' => 'Genting adalah pusat kerajinan tangan Desa Getas. Anyaman bambu buatan warga Genting dikenal hingga tingkat provinsi.', 'hero_img' => 'https://images.unsplash.com/photo-1546845776-dcdf70fd09e3?w=800&h=500&fit=crop&auto=format', 'thumbnail' => 'https://images.unsplash.com/photo-1546845776-dcdf70fd09e3?w=400&h=300&fit=crop&auto=format', 'keunggulan' => ['Pusat kerajinan anyaman bambu', 'Workshop batik tulis', 'Pasar seni mingguan'], 'galeri' => ['https://images.unsplash.com/photo-1546845776-dcdf70fd09e3?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1586717799252-bd134ad00e26?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&h=400&fit=crop&auto=format']],
            ['nama' => 'Metep', 'rw' => 'RW 05', 'jumlah_rt' => 2, 'jumlah_penduduk' => 398, 'luas_wilayah' => '1,8 km²', 'deskripsi' => 'Dekat air terjun alami, destinasi hiking favorit warga.', 'detail' => 'Metep menjadi pintu masuk utama menuju Air Terjun Getas yang tersembunyi di balik bukit. Jalur hiking sepanjang 3 km melewati dusun ini menjadi favorit wisatawan alam.', 'hero_img' => 'https://images.unsplash.com/photo-1637993921206-cae1c2cbba20?w=800&h=500&fit=crop&auto=format', 'thumbnail' => 'https://images.unsplash.com/photo-1637993921206-cae1c2cbba20?w=400&h=300&fit=crop&auto=format', 'keunggulan' => ['Gerbang air terjun tersembunyi', 'Jalur hiking 3 km', 'Pemandu wisata lokal'], 'galeri' => ['https://images.unsplash.com/photo-1637993921206-cae1c2cbba20?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1554931670-4ebfabf6e7a9?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1582583088753-afb19907963a?w=600&h=400&fit=crop&auto=format']],
            ['nama' => 'Bleder', 'rw' => 'RW 06', 'jumlah_rt' => 2, 'jumlah_penduduk' => 421, 'luas_wilayah' => '1,3 km²', 'deskripsi' => 'Titik awal jalur tubing Sungai Blukar yang terkenal.', 'detail' => 'Bleder adalah titik start utama wisata tubing Sungai Blukar. Pengelola wisata tubing terbesar bermarkas di dusun ini.', 'hero_img' => 'https://images.unsplash.com/photo-1719380959727-b240fc7c77de?w=800&h=500&fit=crop&auto=format', 'thumbnail' => 'https://images.unsplash.com/photo-1719380959727-b240fc7c77de?w=400&h=300&fit=crop&auto=format', 'keunggulan' => ['Start point tubing Sungai Blukar', 'Fasilitas wisata terlengkap', 'Penginapan warga tersedia'], 'galeri' => ['https://images.unsplash.com/photo-1719380959727-b240fc7c77de?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1546058914-5000137323f0?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1709025876683-b252a617ab17?w=600&h=400&fit=crop&auto=format']],
            ['nama' => 'Getas', 'rw' => 'RW 07', 'jumlah_rt' => 3, 'jumlah_penduduk' => 478, 'luas_wilayah' => '1,0 km²', 'deskripsi' => 'Pusat pemerintahan dan balai desa berada di sini.', 'detail' => 'Dusun Getas adalah jantung Desa Getas — lokasi balai desa, kantor pelayanan, dan pusat kegiatan masyarakat.', 'hero_img' => 'https://images.unsplash.com/photo-1646928232133-8b2e82546057?w=800&h=500&fit=crop&auto=format', 'thumbnail' => 'https://images.unsplash.com/photo-1646928232133-8b2e82546057?w=400&h=300&fit=crop&auto=format', 'keunggulan' => ['Pusat pemerintahan desa', 'Pasar desa setiap Minggu', 'Lapangan olahraga utama'], 'galeri' => ['https://images.unsplash.com/photo-1646928232133-8b2e82546057?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&h=400&fit=crop&auto=format']],
            ['nama' => 'Truko', 'rw' => 'RW 08', 'jumlah_rt' => 2, 'jumlah_penduduk' => 362, 'luas_wilayah' => '1,2 km²', 'deskripsi' => 'Dusun nelayan kecil di bantaran sungai yang tenang.', 'detail' => 'Truko berbatasan langsung dengan sungai dan dikenal sebagai dusun dengan pemandangan sungai paling indah.', 'hero_img' => 'https://images.unsplash.com/photo-1709025876683-b252a617ab17?w=800&h=500&fit=crop&auto=format', 'thumbnail' => 'https://images.unsplash.com/photo-1709025876683-b252a617ab17?w=400&h=300&fit=crop&auto=format', 'keunggulan' => ['Tepi sungai paling indah', 'Spot foto sunset terbaik', 'Kuliner pecel lele khas'], 'galeri' => ['https://images.unsplash.com/photo-1709025876683-b252a617ab17?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1561774711-b0fa364863b7?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1643215721864-cd4c354ac298?w=600&h=400&fit=crop&auto=format']],
            ['nama' => 'Sanggar', 'rw' => 'RW 09', 'jumlah_rt' => 2, 'jumlah_penduduk' => 334, 'luas_wilayah' => '1,3 km²', 'deskripsi' => 'Sentra seni budaya dan pertunjukan tradisional desa.', 'detail' => 'Sanggar adalah dusun dengan tradisi seni budaya paling kuat. Kelompok seni kuda lumping, rebana, dan wayang kulit aktif berlatih di sini.', 'hero_img' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=800&h=500&fit=crop&auto=format', 'thumbnail' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=400&h=300&fit=crop&auto=format', 'keunggulan' => ['Kelompok seni kuda lumping', 'Pentas budaya rutin', 'Sanggar tari tradisional'], 'galeri' => ['https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=600&h=400&fit=crop&auto=format']],
            ['nama' => 'Banjaran', 'rw' => 'RW 10', 'jumlah_rt' => 3, 'jumlah_penduduk' => 694, 'luas_wilayah' => '1,5 km²', 'deskripsi' => 'Dusun paling hijau — dikelilingi kebun kopi dan cengkeh.', 'detail' => 'Banjaran adalah dusun terbesar sekaligus terhijau di Desa Getas. Kebun kopi arabika dan cengkeh menyelimuti hampir seluruh wilayahnya.', 'hero_img' => 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?w=800&h=500&fit=crop&auto=format', 'thumbnail' => 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?w=400&h=300&fit=crop&auto=format', 'keunggulan' => ['Kebun kopi arabika terluas', 'Produksi cengkeh terbesar', 'Dusun terbesar berpenduduk'], 'galeri' => ['https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=600&h=400&fit=crop&auto=format', 'https://images.unsplash.com/photo-1601493700631-2b16ec4b4716?w=600&h=400&fit=crop&auto=format']],
        ];

        foreach ($dusunData as $i => $d) {
            $keunggulan = $d['keunggulan'];
            $galeri = $d['galeri'];
            unset($d['keunggulan'], $d['galeri']);
            $d['created_by'] = $superadmin->id;
            $dusun = Dusun::create($d);
            foreach ($keunggulan as $j => $k) {
                DusunKeunggulan::create(['dusun_id' => $dusun->id, 'keunggulan' => $k, 'urutan' => $j]);
            }
            foreach ($galeri as $j => $g) {
                DusunGallery::create(['dusun_id' => $dusun->id, 'image_url' => $g, 'urutan' => $j]);
            }
        }

        // Tour Packages
        $pkg1 = TourPackage::create([
            'nama' => 'Tubing Adventure',
            'deskripsi' => 'Menyusuri Sungai Blukar sepanjang 1,5 km dengan arus alami.',
            'harga' => 75000, 'satuan' => 'orang', 'tag' => 'Terpopuler',
            'durasi' => '±2 jam', 'min_participants' => 1, 'max_participants' => 10,
            'gambar' => 'https://images.unsplash.com/photo-1546058914-5000137323f0?w=500&h=320&fit=crop&auto=format',
            'created_by' => $superadmin->id,
        ]);
        foreach (['Pelampung & helm', 'Pemandu lokal', 'Air minum'] as $i => $item) {
            TourPackageInclude::create(['package_id' => $pkg1->id, 'item' => $item, 'urutan' => $i]);
        }

        $pkg2 = TourPackage::create([
            'nama' => 'River Exploration',
            'deskripsi' => 'Eksplorasi sungai bersama guide berpengalaman dan safety equipment lengkap.',
            'harga' => 95000, 'satuan' => 'orang', 'tag' => null,
            'durasi' => '±3 jam', 'min_participants' => 1, 'max_participants' => 8,
            'gambar' => 'https://images.unsplash.com/photo-1561774711-b0fa364863b7?w=500&h=320&fit=crop&auto=format',
            'created_by' => $superadmin->id,
        ]);
        foreach (['Full safety gear', 'Pemandu senior', 'Foto dokumentasi', 'Air minum'] as $i => $item) {
            TourPackageInclude::create(['package_id' => $pkg2->id, 'item' => $item, 'urutan' => $i]);
        }

        $pkg3 = TourPackage::create([
            'nama' => 'Family Package',
            'deskripsi' => 'Paket keluarga lengkap — tubing, makan siang, foto dokumentasi.',
            'harga' => 250000, 'satuan' => 'grup', 'tag' => 'Promo',
            'durasi' => '½ hari', 'min_participants' => 2, 'max_participants' => 6,
            'gambar' => 'https://images.unsplash.com/photo-1520329612326-d6038d1395a1?w=500&h=320&fit=crop&auto=format',
            'created_by' => $superadmin->id,
        ]);
        foreach (['Full safety gear', 'Pemandu keluarga', 'Makan siang', 'Foto & video', 'Suvenir'] as $i => $item) {
            TourPackageInclude::create(['package_id' => $pkg3->id, 'item' => $item, 'urutan' => $i]);
        }

        $pkg4 = TourPackage::create([
            'nama' => 'Group Package',
            'deskripsi' => 'Paket rombongan minimal 20 orang dengan guide dan makan siang.',
            'harga' => 65000, 'satuan' => 'orang', 'tag' => null,
            'durasi' => '½ hari', 'min_participants' => 20, 'max_participants' => 100,
            'gambar' => 'https://images.unsplash.com/photo-1643215721864-cd4c354ac298?w=500&h=320&fit=crop&auto=format',
            'created_by' => $superadmin->id,
        ]);
        foreach (['Safety equipment', 'Multiple guide', 'Makan siang', 'Area gathering'] as $i => $item) {
            TourPackageInclude::create(['package_id' => $pkg4->id, 'item' => $item, 'urutan' => $i]);
        }

        // Booking Sessions
        $sessions = ['Pagi', 'Siang', 'Sore'];
        foreach ([$pkg1, $pkg2] as $pkg) {
            for ($d = 1; $d <= 30; $d++) {
                foreach ($sessions as $sesi) {
                    BookingSession::create([
                        'package_id' => $pkg->id,
                        'tanggal' => now()->addDays($d)->toDateString(),
                        'sesi' => $sesi,
                        'kuota' => 20,
                        'terisi' => rand(0, 5),
                        'created_by' => $superadmin->id,
                    ]);
                }
            }
        }

        // UMKM Products
        $umkmItems = [
            ['nama' => 'Tempe Besem Bu Kartini', 'kategori' => 'Makanan', 'harga' => 5000, 'deskripsi' => 'Tempe besem khas Getas, fermentasi sempurna.', 'gambar' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=300&fit=crop&auto=format', 'no_wa_penjual' => '62812345001'],
            ['nama' => 'Keripik Singkong Aneka Rasa', 'kategori' => 'Makanan', 'harga' => 15000, 'deskripsi' => 'Keripik singkong renyah aneka rasa.', 'gambar' => 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=400&h=300&fit=crop&auto=format', 'no_wa_penjual' => '62812345002'],
            ['nama' => 'Anyaman Bambu Pak Rejo', 'kategori' => 'Kerajinan', 'harga' => 45000, 'deskripsi' => 'Anyaman bambu kualitas ekspor.', 'gambar' => 'https://images.unsplash.com/photo-1586717799252-bd134ad00e26?w=400&h=300&fit=crop&auto=format', 'no_wa_penjual' => '62812345003'],
            ['nama' => 'Beras Organik Pak Triyono', 'kategori' => 'Pertanian', 'harga' => 18000, 'deskripsi' => 'Beras organik asli Getas per kg.', 'gambar' => 'https://images.unsplash.com/photo-1536304929831-ee1ca9d44906?w=400&h=300&fit=crop&auto=format', 'no_wa_penjual' => '62812345005'],
            ['nama' => 'Kopi Arabika Getas', 'kategori' => 'Oleh-Oleh', 'harga' => 65000, 'deskripsi' => 'Kopi arabika premium 200g.', 'gambar' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400&h=300&fit=crop&auto=format', 'no_wa_penjual' => '62812345007'],
            ['nama' => 'Sirup Jahe Madu Bu Endang', 'kategori' => 'Oleh-Oleh', 'harga' => 30000, 'deskripsi' => 'Sirup jahe madu sehat.', 'gambar' => 'https://images.unsplash.com/photo-1601493700631-2b16ec4b4716?w=400&h=300&fit=crop&auto=format', 'no_wa_penjual' => '62812345008'],
        ];
        foreach ($umkmItems as $u) {
            UmkmProduct::create(array_merge($u, ['created_by' => $superadmin->id]));
        }

        // Budaya
        $budayaItems = [
            ['judul' => 'Kuda Lumping', 'kategori' => 'Seni Pertunjukan', 'deskripsi' => 'Tarian tradisional kuda lumping yang digelar setiap peringatan hari besar dan acara adat desa.', 'gambar' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=700&h=460&fit=crop&auto=format', 'span_grid' => 2],
            ['judul' => 'Batik Tulis Getas', 'kategori' => 'Kerajinan Tradisional', 'deskripsi' => 'Batik tulis tangan bermotif sungai dan alam, warisan leluhur yang terus dilestarikan.', 'gambar' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=300&fit=crop&auto=format', 'span_grid' => 1],
            ['judul' => 'Anyaman Bambu', 'kategori' => 'Kerajinan Tradisional', 'deskripsi' => 'Kerajinan anyaman bambu turun-temurun yang menjadi sumber penghidupan warga Dusun Genting.', 'gambar' => 'https://images.unsplash.com/photo-1586717799252-bd134ad00e26?w=400&h=300&fit=crop&auto=format', 'span_grid' => 1],
            ['judul' => 'Pesta Panen & Sedekah Bumi', 'kategori' => 'Upacara Adat', 'deskripsi' => 'Tradisi syukur atas hasil bumi yang digelar setiap tahun dengan arak-arakan dan doa bersama.', 'gambar' => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=400&h=300&fit=crop&auto=format', 'span_grid' => 1],
            ['judul' => 'Sanggar Tari Tradisional', 'kategori' => 'Seni Pertunjukan', 'deskripsi' => 'Sanggar aktif melatih generasi muda dalam tari-tarian Jawa, rebana, dan seni wayang.', 'gambar' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=400&h=300&fit=crop&auto=format', 'span_grid' => 1],
            ['judul' => 'Perkebunan & Agraris', 'kategori' => 'Kearifan Lokal', 'deskripsi' => 'Sistem pertanian organik berbasis kearifan lokal yang diwariskan secara turun-temurun.', 'gambar' => 'https://images.unsplash.com/photo-1683506684881-efbb5203eacf?w=400&h=300&fit=crop&auto=format', 'span_grid' => 1],
        ];
        foreach ($budayaItems as $b) {
            Budaya::create(array_merge($b, ['created_by' => $superadmin->id]));
        }

        // Budaya Schedules
        $kudaLumping = Budaya::where('judul', 'Kuda Lumping')->first();
        if ($kudaLumping) {
            BudayaSchedule::create(['budaya_id' => $kudaLumping->id, 'nama_acara' => 'Kuda Lumping', 'hari' => 'Setiap bulan Suro & hari nasional', 'jam' => '08.00–12.00 WIB', 'deskripsi' => 'Pertunjukan kuda lumping terbuka untuk umum.']);
            BudayaSchedule::create(['budaya_id' => $kudaLumping->id, 'nama_acara' => 'Workshop Batik Tulis', 'hari' => 'Sabtu–Minggu', 'jam' => '08.00–12.00 WIB', 'deskripsi' => 'Belajar membatik bersama pengrajin lokal.']);
        }
        $sanggar = Budaya::where('judul', 'Sanggar Tari Tradisional')->first();
        if ($sanggar) {
            BudayaSchedule::create(['budaya_id' => $sanggar->id, 'nama_acara' => 'Pentas Seni Malam Jumat', 'hari' => 'Setiap Jumat malam', 'jam' => '19.00–21.00 WIB', 'deskripsi' => 'Pentas seni rutin di Dusun Sanggar.']);
        }

        // Village Profile
        VillageProfile::create(['tipe' => 'sejarah', 'judul' => 'Sejarah Desa Getas', 'konten' => 'Desa Getas di Kecamatan Singorojo, Kabupaten Kendal berdiri sejak sekitar tahun 1850. Desa ini dianugerahi keindahan Sungai Blukar yang mengalir jernih, bentangan persawahan organik yang subur, dan pesona alam yang asri. Dengan gotong royong warga, wisata tubing Sungai Blukar di Desa Getas telah berkembang pesat dan berhasil meraih penghargaan Desa Wisata Terbaik tingkat Kabupaten Kendal pada tahun 2025.', 'urutan' => 1, 'created_by' => $superadmin->id]);
        VillageProfile::create(['tipe' => 'visi', 'judul' => 'Visi Desa', 'konten' => '"Desa Getas Maju, Mandiri, dan Sejahtera Berbasis Kearifan Lokal dan Teknologi Digital"', 'urutan' => 2, 'created_by' => $superadmin->id]);
        VillageProfile::create(['tipe' => 'misi', 'judul' => 'Misi Desa', 'konten' => "1. Meningkatkan infrastruktur dan kebersihan area pariwisata alam.\n2. Mengoptimalkan potensi beras organik dan produk anyaman bambu khas warga.\n3. Menghadirkan pelayanan administrasi publik yang cepat berbasis teknologi digital.", 'urutan' => 3, 'created_by' => $superadmin->id]);
        VillageProfile::create(['tipe' => 'pemerintahan', 'judul' => 'Perangkat Desa', 'konten' => 'Kepala Desa: Suyitno, S.Pd.\nSekretaris: Supartini\nKasi Layanan: Dwi Lestari', 'urutan' => 4, 'created_by' => $superadmin->id]);

        // Village Stats
        $stats = [
            ['label' => 'Total Penduduk', 'nilai' => '4.287', 'satuan' => 'jiwa', 'icon' => 'Users', 'urutan' => 1],
            ['label' => 'Jumlah KK', 'nilai' => '1.156', 'satuan' => 'KK', 'icon' => 'Home', 'urutan' => 2],
            ['label' => 'UMKM Aktif', 'nilai' => '62', 'satuan' => 'unit', 'icon' => 'Briefcase', 'urutan' => 3],
            ['label' => 'Wisatawan/Tahun', 'nilai' => '8.500+', 'satuan' => '', 'icon' => 'Mountain', 'urutan' => 4],
            ['label' => 'Luas Wilayah', 'nilai' => '12,4', 'satuan' => 'km²', 'icon' => 'TreePine', 'urutan' => 5],
        ];
        foreach ($stats as $s) {
            VillageStat::create($s);
        }

        // Settings
        Setting::create(['key' => 'wa_admin', 'value' => '6281234567890', 'deskripsi' => 'Nomor WhatsApp admin']);
        Setting::create(['key' => 'nama_desa', 'value' => 'Desa Getas', 'deskripsi' => 'Nama desa']);
        Setting::create(['key' => 'alamat_desa', 'value' => 'Jl. Raya Getas No. 1, Kec. Singorojo, Kab. Kendal 51382', 'deskripsi' => 'Alamat desa']);
    }
}
