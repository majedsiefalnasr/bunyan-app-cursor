<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'name_ar' => 'مواد بناء',
                'name_en' => 'Building materials',
                'slug' => 'building-materials',
            ],
            [
                'name_ar' => 'كهرباء',
                'name_en' => 'Electrical',
                'slug' => 'electrical',
            ],
            [
                'name_ar' => 'سباكة',
                'name_en' => 'Plumbing',
                'slug' => 'plumbing',
            ],
            [
                'name_ar' => 'تشطيبات',
                'name_en' => 'Finishes',
                'slug' => 'finishes',
            ],
        ];

        foreach ($definitions as $index => $def) {
            Category::query()->updateOrCreate(
                ['slug' => $def['slug']],
                [
                    'parent_id' => null,
                    'name_ar' => $def['name_ar'],
                    'name_en' => $def['name_en'],
                    'icon' => null,
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
