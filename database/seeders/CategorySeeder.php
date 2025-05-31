<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Электроника',
                'description' => 'Смартфоны, ноутбуки, планшеты и другая техника',
                'image' => 'images/categories/category-400.svg',
            ],
            [
                'name' => 'Одежда',
                'description' => 'Мужская, женская и детская одежда',
                'image' => 'images/categories/category-400.svg',
            ],
            [
                'name' => 'Бытовая техника',
                'description' => 'Техника для дома и кухни',
                'image' => 'images/categories/category-400.svg',
            ],
            [
                'name' => 'Спорт и отдых',
                'description' => 'Спортивные товары, тренажеры и товары для отдыха',
                'image' => 'images/categories/category-400.svg',
            ],
            [
                'name' => 'Товары для дома',
                'description' => 'Мебель, декор и другие товары для дома',
                'image' => 'images/categories/category-400.svg',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
