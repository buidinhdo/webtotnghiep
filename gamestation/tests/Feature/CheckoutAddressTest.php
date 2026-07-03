<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutAddressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Fake geocoding and routing APIs
        Http::fake([
            'https://nominatim.openstreetmap.org/*' => Http::response([
                [
                    'lat' => '10.7769',
                    'lon' => '106.7009'
                ]
            ], 200),
            'https://router.project-osrm.org/*' => Http::response([
                'routes' => [
                    [
                        'distance' => 5000 // 5 km
                    ]
                ]
            ], 200)
        ]);
    }

    public function test_checkout_page_renders_address_form(): void
    {
        $user = User::factory()->create();
        $cart = Cart::create(['user_id' => $user->id]);
        
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category'
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Game',
            'slug' => 'test-game',
            'platform' => 'PS5',
            'sku' => 'TG-PS5',
            'price' => 100000,
            'stock' => 10,
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100000
        ]);

        $response = $this->actingAs($user)->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertSee('shipping_name');
        $response->assertSee('shipping_phone');
        $response->assertSee('province');
        $response->assertSee('district');
        $response->assertSee('ward');
    }

    public function test_calculate_shipping_api_returns_correct_fee(): void
    {
        $user = User::factory()->create();
        Cart::create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson(route('checkout.calculate-shipping'), [
            'shipping_address' => '123 Nguyễn Huệ, Bến Nghé, Quận 1, Hồ Chí Minh',
            'shipping_method' => 'standard'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'distance_km' => 5.0,
                'shipping_fee' => 16250
            ]);
    }

    public function test_placing_order_successfully(): void
    {
        $user = User::factory()->create();
        $cart = Cart::create(['user_id' => $user->id]);
        
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category'
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Game',
            'slug' => 'test-game',
            'platform' => 'PS5',
            'sku' => 'TG-PS5',
            'price' => 100000,
            'stock' => 10,
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100000
        ]);

        $response = $this->actingAs($user)->post(route('checkout.place'), [
            'shipping_name' => 'Alice Smith',
            'shipping_phone' => '0912345678',
            'province' => 'Hà Nội',
            'district' => 'Hoàn Kiếm',
            'ward' => 'Tràng Tiền',
            'detail' => '456 Lý Thái Tổ',
            'shipping_method' => 'standard',
            'payment_method' => 'cod',
            'notes' => 'Giao giờ hành chính'
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'shipping_name' => 'Alice Smith',
            'shipping_phone' => '0912345678',
            'shipping_address' => '456 Lý Thái Tổ, Tràng Tiền, Hoàn Kiếm, Hà Nội',
            'shipping_method' => 'standard',
            'payment_method' => 'cod',
            'notes' => 'Giao giờ hành chính'
        ]);
    }
}
