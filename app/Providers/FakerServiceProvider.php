<?php

namespace App\Providers;

use Faker\Generator;
use Illuminate\Support\ServiceProvider;

class FakerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Generator::class, function ($app) {
            $faker = \Faker\Factory::create($app['config']->get('app.faker_locale', 'en_US'));
            
            // Переопределение метода imageUrl для использования другого сервиса
            $faker->extend('imageUrl', function () use ($faker) {
                return function (
                    $width = 640,
                    $height = 480,
                    $category = null,
                    $randomize = true,
                    $word = null,
                    $gray = false,
                    $format = 'png'
                ) use ($faker) {
                    // Используем picsum.photos вместо via.placeholder.com
                    $baseUrl = 'https://picsum.photos';
                    
                    // Формирование URL с учетом параметров
                    return "{$baseUrl}/{$width}/{$height}";
                };
            });
            
            return $faker;
        });
    }
}
