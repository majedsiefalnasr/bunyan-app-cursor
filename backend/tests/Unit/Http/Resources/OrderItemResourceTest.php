<?php

namespace Tests\Unit\Http\Resources;

use App\Http\Resources\Api\V1\OrderItemResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class OrderItemResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_formats_price_using_unit_price_when_present(): void
    {
        $product = Product::factory()->create(['price' => 10]);

        $item = new OrderItem([
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 9.5,
            'unit_price' => 12.345,
        ]);
        $item->id = 123;
        $item->setRelation('product', $product);

        $array = OrderItemResource::make($item)->resolve(Request::create('/'));

        $this->assertSame(123, $array['id']);
        $this->assertSame($product->id, $array['product_id']);
        $this->assertSame(2, $array['quantity']);
        $this->assertSame('12.35', $array['price']);
        $this->assertInstanceOf(ProductResource::class, $array['product']);
        $productArray = $array['product']->toArray(Request::create('/'));
        $this->assertSame($product->id, $productArray['id']);
    }

    public function test_it_falls_back_to_price_when_unit_price_missing(): void
    {
        $item = new OrderItem([
            'product_id' => 5,
            'quantity' => 1,
            'price' => 7.1,
            'unit_price' => null,
        ]);
        $item->id = 1;

        $array = OrderItemResource::make($item)->resolve(Request::create('/'));

        $this->assertSame('7.10', $array['price']);
    }
}
