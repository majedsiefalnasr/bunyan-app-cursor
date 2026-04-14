<?php

namespace Tests\Unit\Repositories;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Product;
use App\Models\SupplierProfile;
use App\Models\User;
use App\Repositories\OrderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(OrderRepository::class);
    }

    public function test_paginate_for_actor_scopes_by_role(): void
    {
        $admin = User::factory()->admin()->create();
        $customerA = User::factory()->customer()->create();
        $customerB = User::factory()->customer()->create();

        $supplier = SupplierProfile::factory()->create();
        $contractor = $supplier->user;

        $product = Product::factory()->create();

        $orderA = Order::factory()->create(['customer_id' => $customerA->id, 'supplier_id' => $supplier->id, 'status' => 'pending']);
        $orderA->items()->create(['product_id' => $product->id, 'variant_id' => null, 'quantity' => 1, 'unit_price' => 10, 'subtotal' => 10]);

        $orderB = Order::factory()->create(['customer_id' => $customerB->id, 'supplier_id' => $supplier->id, 'status' => 'pending']);
        $orderB->items()->create(['product_id' => $product->id, 'variant_id' => null, 'quantity' => 1, 'unit_price' => 10, 'subtotal' => 10]);

        $adminPage = $this->repo->paginateForActor($admin, ['per_page' => 50]);
        $this->assertGreaterThanOrEqual(2, $adminPage->total());

        $customerPage = $this->repo->paginateForActor($customerA, ['per_page' => 50]);
        $this->assertSame(1, $customerPage->total());
        $this->assertSame($orderA->id, $customerPage->items()[0]->id);

        $contractor->refresh();
        $contractorPage = $this->repo->paginateForActor($contractor, ['per_page' => 50]);
        $this->assertSame(2, $contractorPage->total());
    }

    public function test_paginate_for_actor_contractor_without_supplier_profile_gets_none(): void
    {
        $contractor = User::factory()->create(['role' => UserRole::Contractor->value]);
        $page = $this->repo->paginateForActor($contractor, ['per_page' => 50]);
        $this->assertSame(0, $page->total());
    }

    public function test_next_display_sequence_for_date_increments_from_existing_numbers(): void
    {
        $ymd = '20260101';
        Order::factory()->create(['order_number' => "BNY-{$ymd}-0002"]);
        Order::factory()->create(['order_number' => "BNY-{$ymd}-0009"]);
        Order::factory()->create(['order_number' => "BNY-{$ymd}-abcd"]); // ignored

        $next = $this->repo->nextDisplaySequenceForDate($ymd);
        $this->assertSame(10, $next);
    }
}
