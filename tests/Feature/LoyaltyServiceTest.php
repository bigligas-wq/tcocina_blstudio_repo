<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LoyaltySetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\UserLoyaltyMovement;
use App\Models\UserLoyaltyWallet;
use App\Services\LoyaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_awards_stickers_once_per_confirmed_order(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        // category_id=1 es hamburguesas — se fuerza el ID para que coincida con el filtro del service
        $category = Category::firstOrCreate(['id' => 1], ['name' => 'Hamburguesas', 'sort_order' => 1, 'is_active' => true]);
        $product = Product::create([
            'category_id' => 1,
            'name' => 'Burger Test',
            'base_price' => 10000,
            'is_available' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-TESTA01',
            'user_id' => $user->id,
            'address_id' => null,
            'status' => 'confirmed',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'subtotal' => 10000,
            'delivery_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 10000,
            'contact_name' => 'Cliente Test',
            'contact_phone' => '111111111',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 10000,
            'total_price' => 30000,
        ]);

        $service = app(LoyaltyService::class);
        $service->awardFromConfirmedOrder($order);
        $service->awardFromConfirmedOrder($order);

        $wallet = UserLoyaltyWallet::where('user_id', $user->id)->first();

        $this->assertNotNull($wallet);
        $this->assertSame(3, $wallet->current_stickers);
        $this->assertSame(3, $wallet->total_earned);
        $this->assertSame(
            1,
            UserLoyaltyMovement::where('user_id', $user->id)->where('reason', 'order_confirmed')->count()
        );
    }

    public function test_non_burger_items_do_not_award_stickers(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        // Categoría bebidas (id != 1)
        $catBebidas = Category::firstOrCreate(['id' => 3], ['name' => 'Bebidas', 'sort_order' => 3, 'is_active' => true]);
        $product = Product::create([
            'category_id' => 3,
            'name' => 'Coca Cola',
            'base_price' => 2000,
            'is_available' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-TESTB01',
            'user_id' => $user->id,
            'address_id' => null,
            'status' => 'confirmed',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'subtotal' => 6000,
            'delivery_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 6000,
            'contact_name' => 'Cliente Test',
            'contact_phone' => '111111111',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 2000,
            'total_price' => 6000,
        ]);

        $service = app(LoyaltyService::class);
        $service->awardFromConfirmedOrder($order);

        $wallet = UserLoyaltyWallet::where('user_id', $user->id)->first();
        $this->assertNull($wallet, 'No debe crearse wallet si solo hay bebidas/acompañamientos');
        $this->assertSame(
            0,
            UserLoyaltyMovement::where('user_id', $user->id)->count(),
            'No debe registrarse ningún movimiento por items que no son hamburguesas'
        );
    }

    public function test_request_redemption_spends_target_and_keeps_remainder(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        LoyaltySetting::query()->update(['is_active' => false]);
        LoyaltySetting::create([
            'target_stickers' => 10,
            'reward_type' => 'combo',
            'reward_value' => 'Combo clasico',
            'is_active' => true,
        ]);

        UserLoyaltyWallet::create([
            'user_id' => $user->id,
            'current_stickers' => 12,
            'total_earned' => 12,
            'total_redeemed' => 0,
        ]);

        $redemption = app(LoyaltyService::class)->requestRedemption($user);
        $wallet = UserLoyaltyWallet::where('user_id', $user->id)->first();

        $this->assertSame('pending', $redemption->status);
        $this->assertSame(10, $redemption->stickers_spent);
        $this->assertSame(2, $wallet->current_stickers);
        $this->assertSame(10, $wallet->total_redeemed);
        $this->assertDatabaseHas('user_loyalty_movements', [
            'user_id' => $user->id,
            'reason' => 'redemption_requested',
            'delta' => -10,
        ]);
    }

    public function test_request_redemption_fails_if_target_not_reached(): void
    {
        $this->expectException(\RuntimeException::class);

        $user = User::factory()->create(['role' => 'customer']);
        LoyaltySetting::query()->update(['is_active' => false]);
        LoyaltySetting::create([
            'target_stickers' => 20,
            'reward_type' => 'text',
            'reward_value' => 'Premio',
            'is_active' => true,
        ]);

        UserLoyaltyWallet::create([
            'user_id' => $user->id,
            'current_stickers' => 5,
            'total_earned' => 5,
            'total_redeemed' => 0,
        ]);

        app(LoyaltyService::class)->requestRedemption($user);
    }
}
