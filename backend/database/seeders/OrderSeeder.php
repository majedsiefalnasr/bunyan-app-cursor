<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Get users
        $customer1 = User::where('email', 'customer@example.com')->first();
        $customer2 = User::where('email', 'mohammad.alarian@example.com')->first();
        $customer3 = User::where('email', 'fatima.alsahli@example.com')->first();
        $contractor1 = User::where('email', 'contractor@example.com')->first();
        $contractor2 = User::where('email', 'advanced.construction@example.com')->first();

        // Get products - make sure they exist first
        $cementProduct = Product::where('sku', 'CEMENT-001')->first();
        $steelProduct = Product::where('sku', 'STEEL-001')->first();
        $sandProduct = Product::where('sku', 'SAND-001')->first();
        $brickProduct = Product::where('sku', 'BRICK-001')->first();
        $paintProduct = Product::where('sku', 'PAINT-001')->first();

        $orders = [
            // Customer 1 orders
            [
                'customer_id' => $customer1?->id,
                'status' => 'completed',
                'subtotal' => 4050.00,
                'tax_amount' => 450.00,
                'shipping_amount' => 0.00,
                'total_amount' => 4500.00,
                'order_number' => 'ORD-'.now()->subMonths(2)->format('Ymd').'-001',
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
                'items' => [
                    [
                        'product_id' => $cementProduct?->id,
                        'quantity' => 50,
                        'unit_price' => 45.00,
                        'subtotal' => 2250.00,
                    ],
                    [
                        'product_id' => $sandProduct?->id,
                        'quantity' => 1000,
                        'unit_price' => 0.80,
                        'subtotal' => 800.00,
                    ],
                    [
                        'product_id' => $brickProduct?->id,
                        'quantity' => 500,
                        'unit_price' => 0.35,
                        'subtotal' => 175.00,
                    ],
                ],
            ],
            [
                'customer_id' => $customer1?->id,
                'status' => 'completed',
                'subtotal' => 2970.00,
                'tax_amount' => 330.00,
                'shipping_amount' => 0.00,
                'total_amount' => 3300.00,
                'order_number' => 'ORD-'.now()->subMonths(1)->format('Ymd').'-001',
                'created_at' => now()->subMonths(1),
                'updated_at' => now()->subMonths(1),
                'items' => [
                    [
                        'product_id' => $steelProduct?->id,
                        'quantity' => 1000,
                        'unit_price' => 2.50,
                        'subtotal' => 2500.00,
                    ],
                    [
                        'product_id' => $paintProduct?->id,
                        'quantity' => 10,
                        'unit_price' => 30.00,
                        'subtotal' => 300.00,
                    ],
                ],
            ],
            [
                'customer_id' => $customer1?->id,
                'status' => 'pending',
                'subtotal' => 1800.00,
                'tax_amount' => 200.00,
                'shipping_amount' => 0.00,
                'total_amount' => 2000.00,
                'order_number' => 'ORD-'.now()->format('Ymd').'-001',
                'created_at' => now(),
                'updated_at' => now(),
                'items' => [
                    [
                        'product_id' => $cementProduct?->id,
                        'quantity' => 40,
                        'unit_price' => 45.00,
                        'subtotal' => 1800.00,
                    ],
                ],
            ],

            // Customer 2 orders
            [
                'customer_id' => $customer2?->id,
                'status' => 'completed',
                'subtotal' => 13500.00,
                'tax_amount' => 1500.00,
                'shipping_amount' => 0.00,
                'total_amount' => 15000.00,
                'order_number' => 'ORD-'.now()->subMonths(3)->format('Ymd').'-002',
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3),
                'items' => [
                    [
                        'product_id' => $cementProduct?->id,
                        'quantity' => 200,
                        'unit_price' => 45.00,
                        'subtotal' => 9000.00,
                    ],
                    [
                        'product_id' => $steelProduct?->id,
                        'quantity' => 2000,
                        'unit_price' => 2.50,
                        'subtotal' => 5000.00,
                    ],
                ],
            ],
            [
                'customer_id' => $customer2?->id,
                'status' => 'shipped',
                'subtotal' => 7200.00,
                'tax_amount' => 800.00,
                'shipping_amount' => 0.00,
                'total_amount' => 8000.00,
                'order_number' => 'ORD-'.now()->subDays(7)->format('Ymd').'-002',
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
                'items' => [
                    [
                        'product_id' => $sandProduct?->id,
                        'quantity' => 5000,
                        'unit_price' => 0.80,
                        'subtotal' => 4000.00,
                    ],
                    [
                        'product_id' => $brickProduct?->id,
                        'quantity' => 10000,
                        'unit_price' => 0.35,
                        'subtotal' => 3500.00,
                    ],
                ],
            ],

            // Customer 3 orders
            [
                'customer_id' => $customer3?->id,
                'status' => 'completed',
                'subtotal' => 3240.00,
                'tax_amount' => 360.00,
                'shipping_amount' => 0.00,
                'total_amount' => 3600.00,
                'order_number' => 'ORD-'.now()->subMonths(4)->format('Ymd').'-003',
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subMonths(4),
                'items' => [
                    [
                        'product_id' => $cementProduct?->id,
                        'quantity' => 60,
                        'unit_price' => 45.00,
                        'subtotal' => 2700.00,
                    ],
                    [
                        'product_id' => $paintProduct?->id,
                        'quantity' => 3,
                        'unit_price' => 30.00,
                        'subtotal' => 90.00,
                    ],
                ],
            ],

            // Contractor orders
            [
                'customer_id' => $contractor1?->id,
                'status' => 'completed',
                'subtotal' => 22700.00,
                'tax_amount' => 2500.00,
                'shipping_amount' => 0.00,
                'total_amount' => 25200.00,
                'order_number' => 'ORD-'.now()->subMonths(2)->format('Ymd').'-004',
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
                'items' => [
                    [
                        'product_id' => $cementProduct?->id,
                        'quantity' => 500,
                        'unit_price' => 43.00,
                        'subtotal' => 21500.00,
                    ],
                    [
                        'product_id' => $steelProduct?->id,
                        'quantity' => 500,
                        'unit_price' => 2.40,
                        'subtotal' => 1200.00,
                    ],
                ],
            ],
            [
                'customer_id' => $contractor2?->id,
                'status' => 'pending',
                'subtotal' => 10800.00,
                'tax_amount' => 1200.00,
                'shipping_amount' => 0.00,
                'total_amount' => 12000.00,
                'order_number' => 'ORD-'.now()->subDays(3)->format('Ymd').'-005',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
                'items' => [
                    [
                        'product_id' => $sandProduct?->id,
                        'quantity' => 10000,
                        'unit_price' => 0.75,
                        'subtotal' => 7500.00,
                    ],
                    [
                        'product_id' => $brickProduct?->id,
                        'quantity' => 15000,
                        'unit_price' => 0.30,
                        'subtotal' => 4500.00,
                    ],
                ],
            ],
        ];

        foreach ($orders as $orderData) {
            $items = $orderData['items'];
            unset($orderData['items']);

            if ($orderData['customer_id']) {
                $order = Order::updateOrCreate(
                    [
                        'order_number' => $orderData['order_number'],
                    ],
                    $orderData
                );

                // Create order items
                foreach ($items as $itemData) {
                    if ($itemData['product_id']) {
                        OrderItem::updateOrCreate(
                            [
                                'order_id' => $order->id,
                                'product_id' => $itemData['product_id'],
                            ],
                            $itemData
                        );
                    }
                }
            }
        }
    }
}
