<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Product;
use App\Models\Category;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap for IQOS TEREA Sticks store';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sitemap generation...');
        
        $sitemap = Sitemap::create(config('app.url'));
        
        // Add home page
        $sitemap->add(
            Url::create('/')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0)
        );
        
        // Add categories index page
        $sitemap->add(
            Url::create('/categories')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.9)
        );
        
        // Add all categories
        $categories = Category::all();
        foreach ($categories as $category) {
            $sitemap->add(
                Url::create("/category/{$category->slug}")
                    ->setLastModificationDate($category->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
            );
        }
        
        // Add all active products
        $products = Product::where('is_active', true)->get();
        foreach ($products as $product) {
            $sitemap->add(
                Url::create("/product/{$product->slug}")
                    ->setLastModificationDate($product->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.7)
            );
        }
        
        // Add FAQ page
        $sitemap->add(
            Url::create('/faq')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.6)
        );
        
        // Add cart page
        $sitemap->add(
            Url::create('/cart')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.5)
        );
        
        // Add checkout page
        $sitemap->add(
            Url::create('/checkout')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.5)
        );
        
        // Write sitemap to file
        $sitemap->writeToFile(public_path('sitemap.xml'));
        
        $this->info('Sitemap generated successfully!');
        $this->info('Location: ' . public_path('sitemap.xml'));
        
        return Command::SUCCESS;
    }
}
