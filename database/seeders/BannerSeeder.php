<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'image' => 'images/banners/default-banner.svg',
                'text' => 'Добро пожаловать в CruseStick',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'image' => 'images/banners/default-banner.svg',
                'text' => 'Широкий ассортимент качественных товаров',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'image' => 'images/banners/default-banner.svg',
                'text' => 'Специальные предложения для наших клиентов',
                'is_active' => true,
                'order' => 3,
            ],
        ];
        
        foreach ($banners as $banner) {
            Banner::updateOrCreate(
                ['order' => $banner['order']],
                $banner
            );
        }
    }
}
