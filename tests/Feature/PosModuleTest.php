<?php

namespace Tests\Feature;

use App\Models\PosCategory;
use App\Models\PosProduct;
use App\Models\PosTransaction;
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
        $response->assertSee('Keranjang Pesanan');
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
                    'product_id' => $product->id,
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
        ]);

        $this->assertDatabaseHas('pos_transaction_items', [
            'product_id' => $product->id,
            'product_name' => 'Ubi Goreng',
            'quantity' => 2,
            'subtotal' => 20000,
        ]);

        // Stock decreased from 10 to 8
        $this->assertEquals(8, $product->fresh()->stock);
    }

    public function test_pos_receipt_page_rendered(): void
    {
        $transaction = PosTransaction::create([
            'invoice_number' => 'POS-TEST-12345',
            'user_id' => $this->user->id,
            'total_amount' => 15000,
            'paid_amount' => 20000,
            'change_amount' => 5000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)->get(route('admin.pos.receipt', $transaction->id));

        $response->assertStatus(200);
        $response->assertSee('POS-TEST-12345');
        $response->assertSee('DESA WISATA GETAS');
    }
}
