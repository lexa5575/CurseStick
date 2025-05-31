<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\UserAddress;
use App\Models\Favorite;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Категории и товары
        $this->call(CategorySeeder::class);
        
        // Создаем 10 товаров для каждой категории
        $categories = Category::all();
        foreach ($categories as $category) {
            Product::factory(10)->create(['category_id' => $category->id]);
        }

        // Пользователи с адресами и избранным
        User::factory(5)
            ->has(UserAddress::factory()->count(2), 'addresses')
            ->has(Favorite::factory()->count(3), 'favorites')
            ->create();

        // FAQ
        Faq::factory(5)->create();
        
        // Заполняем баннеры и платежные методы
        $this->call([
            BannerSeeder::class,
            PaymentMethodSeeder::class,
        ]);

        // Заказы с товарами и статусами
        Order::factory(20)->create()->each(function ($order) {
            $products = Product::inRandomOrder()->take(rand(1, 5))->get();
            foreach ($products as $product) {
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => rand(1, 3),
                    'price' => $product->price,
                    'discount' => $product->discount,
                ]);
            }
            // История статусов
            $statuses = ['new', 'paid', 'shipped', 'completed'];
            foreach (array_slice($statuses, 0, rand(1, count($statuses))) as $status) {
                $order->statusHistories()->create([
                    'status' => $status,
                ]);
            }
        });
    }
}
