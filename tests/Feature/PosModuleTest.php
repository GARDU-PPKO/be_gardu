<?php

namespace Tests\Feature;

use App\Models\AddOn;
use App\Models\Booking;
use App\Models\BookingSession;
use App\Models\PaketWisata;
use App\Models\PosCategory;
use App\Models\PosProduct;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\UmkmProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class PosModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_pos_terminal_page_can_be_rendered(): void
    {
        $category = PosCategory::create(['name' => 'Makanan', 'slug' => 'makanan']);
        PosProduct::create([
            'category_id' => $category->id,
            'name' => 'Kopi Robusta',
            'sku' => 'POS-001',
            'price' => 10000,
            'stock' => 20,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('admin.pos.index'));

        $response->assertStatus(200);
        $response->assertSee('Kopi Robusta');
        $response->assertSee('Keranjang');
    }

    public function test_pos_checkout_creates_transaction_and_deducts_stock(): void
    {
        $product = PosProduct::create([
            'name' => 'Ubi Goreng',
            'sku' => 'FOOD-001',
            'price' => 10000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $payload = [
            'items' => [
                [
                    'item_type' => 'pos_product',
                    'item_id' => (string) $product->id,
                    'quantity' => 2,
                ]
            ],
            'paid_amount' => 50000,
            'payment_method' => 'cash',
        ];

        $response = $this->actingAs($this->user)->postJson(route('admin.pos.checkout'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'change_amount' => 30000,
        ]);

        $this->assertDatabaseHas('pos_transactions', [
            'user_id' => $this->user->id,
            'total_amount' => 20000,
            'paid_amount' => 50000,
            'change_amount' => 30000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('pos_transaction_items', [
            'item_type' => 'pos_product',
            'item_id' => (string) $product->id,
            'product_name' => 'Ubi Goreng',
            'quantity' => 2,
            'subtotal' => 20000,
        ]);

        // Stock decreased from 10 to 8
        $this->assertEquals(8, $product->fresh()->stock);
    }

    public function test_pos_checkout_umkm_product_deducts_stock(): void
    {
        $umkm = UmkmProduct::create([
            'nama' => 'Tempe Besem Bu Kartini',
            'kategori' => 'Makanan',
            'harga' => 5000,
            'stock' => 10,
            'sku' => 'UMKM-TEST-001',
            'deskripsi' => 'Tempe besem khas Getas.',
            'gambar' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=300&fit=crop&auto=format',
            'no_wa_penjual' => '62812345001',
            'created_by' => $this->user->id,
        ]);

        $payload = [
            'items' => [
                [
                    'item_type' => 'umkm_product',
                    'item_id' => $umkm->id,
                    'quantity' => 3,
                ]
            ],
            'paid_amount' => 20000,
            'payment_method' => 'cash',
        ];

        $response = $this->actingAs($this->user)->postJson(route('admin.pos.checkout'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'change_amount' => 5000,
        ]);

        $this->assertDatabaseHas('pos_transaction_items', [
            'item_type' => 'umkm_product',
            'item_id' => $umkm->id,
            'product_name' => 'Tempe Besem Bu Kartini',
            'quantity' => 3,
            'subtotal' => 15000,
        ]);

        // Stock decreased from 10 to 7
        $this->assertEquals(7, $umkm->fresh()->stock);
    }

    public function test_pos_checkout_paket_wisata_creates_booking(): void
    {
        $paket = PaketWisata::create([
            'nama' => 'Genta Gempi Solo',
            'kategori' => 'camping',
            'tipe_harga' => 'per_paket_fixed',
            'harga_paket' => 80000,
            'kapasitas_per_unit' => 1,
            'deskripsi' => 'Camping solo.',
            'aktif' => true,
            'created_by' => $this->user->id,
        ]);

        BookingSession::create([
            'sesi' => 'Pagi',
            'jam_mulai' => '08:00',
            'jam_selesai' => '11:00',
            'kuota' => 30,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $visitDate = now()->addDays(3)->toDateString();

        $payload = [
            'items' => [
                [
                    'item_type' => 'paket_wisata',
                    'item_id' => (string) $paket->id,
                    'quantity' => 2,
                ]
            ],
            'paid_amount' => 160000,
            'payment_method' => 'cash',
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '62812345678',
            'visit_date' => $visitDate,
            'sesi' => 'Pagi',
        ];

        $response = $this->actingAs($this->user)->postJson(route('admin.pos.checkout'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'change_amount' => 0,
        ]);

        // Booking walk-in dibuat & langsung CONFIRMED
        $this->assertDatabaseHas('bookings', [
            'nama_lengkap' => 'Budi Santoso',
            'no_whatsapp' => '62812345678',
            'paket_wisata_id' => $paket->id,
            'sesi' => 'Pagi',
            'jumlah_peserta' => 2,
            'total_harga' => 160000,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $booking = Booking::where('nama_lengkap', 'Budi Santoso')->first();
        $this->assertEquals($visitDate, $booking->tanggal_kunjungan->format('Y-m-d'));

        // Item transaksi terhubung ke booking
        $item = PosTransactionItem::where('item_type', 'paket_wisata')->first();
        $this->assertNotNull($item);
        $this->assertNotNull($item->booking_id);
        $this->assertEquals(160000, (int) $item->subtotal);
    }

    public function test_pos_checkout_mixed_items(): void
    {
        $product = PosProduct::create([
            'name' => 'Kopi Robusta',
            'sku' => 'DRINK-001',
            'price' => 10000,
            'stock' => 20,
            'is_active' => true,
        ]);

        $umkm = UmkmProduct::create([
            'nama' => 'Kopi Arabika Getas',
            'kategori' => 'Oleh-Oleh',
            'harga' => 65000,
            'stock' => 5,
            'sku' => 'UMKM-TEST-002',
            'deskripsi' => 'Kopi arabika premium.',
            'gambar' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400&h=300&fit=crop&auto=format',
            'no_wa_penjual' => '62812345007',
            'created_by' => $this->user->id,
        ]);

        $payload = [
            'items' => [
                ['item_type' => 'pos_product', 'item_id' => (string) $product->id, 'quantity' => 2],
                ['item_type' => 'umkm_product', 'item_id' => $umkm->id, 'quantity' => 1],
            ],
            'paid_amount' => 100000,
            'payment_method' => 'cash',
        ];

        $response = $this->actingAs($this->user)->postJson(route('admin.pos.checkout'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'change_amount' => 15000, // 100000 - (20000 + 65000)
        ]);

        $this->assertDatabaseHas('pos_transactions', [
            'total_amount' => 85000,
            'change_amount' => 15000,
        ]);

        $this->assertEquals(18, $product->fresh()->stock);
        $this->assertEquals(4, $umkm->fresh()->stock);
    }

    public function test_pos_cancel_transaction_restores_stock_and_cancels_booking(): void
    {
        // Transaksi campuran: pos product + umkm + paket
        $product = PosProduct::create([
            'name' => 'Ubi Goreng',
            'sku' => 'FOOD-001',
            'price' => 10000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $paket = PaketWisata::create([
            'nama' => 'Genta Gempi Solo',
            'kategori' => 'camping',
            'tipe_harga' => 'per_paket_fixed',
            'harga_paket' => 80000,
            'kapasitas_per_unit' => 1,
            'deskripsi' => 'Camping solo.',
            'aktif' => true,
            'created_by' => $this->user->id,
        ]);

        BookingSession::create([
            'sesi' => 'Siang',
            'jam_mulai' => '11:00',
            'jam_selesai' => '14:00',
            'kuota' => 30,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $visitDate = now()->addDays(5)->toDateString();

        $payload = [
            'items' => [
                ['item_type' => 'pos_product', 'item_id' => (string) $product->id, 'quantity' => 2],
                ['item_type' => 'paket_wisata', 'item_id' => (string) $paket->id, 'quantity' => 1],
            ],
            'paid_amount' => 100000,
            'payment_method' => 'cash',
            'customer_name' => 'Siti Nurhaliza',
            'customer_phone' => '62821234567',
            'visit_date' => $visitDate,
            'sesi' => 'Siang',
        ];

        $this->actingAs($this->user)->postJson(route('admin.pos.checkout'), $payload)
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $transaction = PosTransaction::latest('id')->first();
        $this->assertNotNull($transaction);

        $booking = Booking::where('nama_lengkap', 'Siti Nurhaliza')->first();
        $this->assertNotNull($booking);
        $this->assertEquals(Booking::STATUS_CONFIRMED, $booking->status);

        // Batalkan transaksi
        $response = $this->actingAs($this->user)->post(route('admin.pos.cancel', $transaction->id));
        $response->assertSessionHas('success');

        // Status transaksi berubah
        $this->assertEquals('cancelled', $transaction->fresh()->status);

        // Stok pos product dikembalikan 8 -> 10
        $this->assertEquals(10, $product->fresh()->stock);

        // Booking dibatalkan
        $this->assertEquals(Booking::STATUS_CANCELLED, $booking->fresh()->status);
    }

    public function test_pos_receipt_page_rendered(): void
    {
        $transaction = PosTransaction::create([
            'invoice_number' => 'POS-TEST-12345',
            'user_id' => $this->user->id,
            'total_amount' => 15000,
            'paid_amount' => 20000,
            'change_amount' => 5000,
            'payment_method' => 'cash'  ,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)->get(route('admin.pos.receipt', $transaction->id));

        $response->assertStatus(200);
        $response->assertSee('POS-TEST-12345');
        $response->assertSee('DESA WISATA GETAS');
    }

    public function test_pos_checkout_with_addon(): void
    {
        $makan = AddOn::create([
            'nama' => 'Makan Siang Prasmanan',
            'tipe_harga' => 'per_orang',
            'harga' => 25000,
            'aktif' => true,
        ]);

        $payload = [
            'items' => [
                [
                    'item_type' => 'addon',
                    'item_id' => (string) $makan->id,
                    'quantity' => 4,
                ],
            ],
            'paid_amount' => 100000,
            'payment_method' => 'cash',
        ];

        $response = $this->actingAs($this->user)->postJson(route('admin.pos.checkout'), $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $transaction = PosTransaction::latest('id')->first();
        $this->assertNotNull($transaction);
        $this->assertEquals(100000, (float) $transaction->total_amount);

        $this->assertDatabaseHas('pos_transaction_items', [
            'transaction_id' => $transaction->id,
            'item_type' => 'addon',
            'item_id' => (string) $makan->id,
            'quantity' => 4,
            'subtotal' => 100000,
        ]);
    }
}

