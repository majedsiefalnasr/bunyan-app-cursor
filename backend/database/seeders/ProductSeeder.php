<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'إسمنت برتلاندي',
                'description' => 'إسمنت برتلاندي عالي الجودة للبناء',
                'sku' => 'CEMENT-001',
                'price' => 45.00,
                'quantity_in_stock' => 1000,
                'category' => 'مواد البناء الأساسية',
                'active' => true,
            ],
            [
                'name' => 'حديد تسليح',
                'description' => 'حديد تسليح بأقطار مختلفة',
                'sku' => 'STEEL-001',
                'price' => 2.50,
                'quantity_in_stock' => 500,
                'category' => 'الحديد والصلب',
                'active' => true,
            ],
            [
                'name' => 'رمل بناء',
                'description' => 'رمل طبيعي نظيف للبناء',
                'sku' => 'SAND-001',
                'price' => 0.80,
                'quantity_in_stock' => 2000,
                'category' => 'الرمل والحصى',
                'active' => true,
            ],
            [
                'name' => 'حصى بناء',
                'description' => 'حصى مغسول مقاس 15-20 ملم',
                'sku' => 'GRAVEL-001',
                'price' => 1.00,
                'quantity_in_stock' => 1500,
                'category' => 'الرمل والحصى',
                'active' => true,
            ],
            [
                'name' => 'الطوب الأحمر',
                'description' => 'طوب أحمر عالي الجودة للحوائط',
                'sku' => 'BRICK-001',
                'price' => 0.35,
                'quantity_in_stock' => 5000,
                'category' => 'الطوب والبلاط',
                'active' => true,
            ],
            [
                'name' => 'سيراميك أرضيات',
                'description' => 'سيراميك أرضيات فاخرة',
                'sku' => 'CERAMIC-001',
                'price' => 25.00,
                'quantity_in_stock' => 300,
                'category' => 'الطوب والبلاط',
                'active' => true,
            ],
            [
                'name' => 'جبس بورد',
                'description' => 'ألواح جبس بورد للديكور الداخلي',
                'sku' => 'GYPSUM-001',
                'price' => 15.00,
                'quantity_in_stock' => 400,
                'category' => 'مواد العزل والديكور',
                'active' => true,
            ],
            [
                'name' => 'طلاء داخلي',
                'description' => 'طلاء عالي الجودة للجدران الداخلية',
                'sku' => 'PAINT-001',
                'price' => 30.00,
                'quantity_in_stock' => 200,
                'category' => 'الدهانات والمعالجات',
                'active' => true,
            ],
            [
                'name' => 'أسياخ حديدية',
                'description' => 'أسياخ حديدية ملساء بقطر 12 ملم',
                'sku' => 'RODS-001',
                'price' => 3.50,
                'quantity_in_stock' => 600,
                'category' => 'الحديد والصلب',
                'active' => true,
            ],
            [
                'name' => 'زجاج شفاف',
                'description' => 'زجاج شفاف عادي 3 ملم',
                'sku' => 'GLASS-001',
                'price' => 8.00,
                'quantity_in_stock' => 100,
                'category' => 'الزجاج والنوافذ',
                'active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['sku' => $product['sku']], $product);
        }
    }
}
