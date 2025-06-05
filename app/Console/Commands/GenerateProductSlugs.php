<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateProductSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:generate-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate slugs for existing products where slug is null';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting slug generation for products with null slugs...');

        $productsToUpdate = Product::whereNull('slug')->get();

        if ($productsToUpdate->isEmpty()) {
            $this->info('No products found requiring slug generation.');
            return Command::SUCCESS;
        }

        $this->info('Found ' . $productsToUpdate->count() . ' products to update.');
        $progressBar = $this->output->createProgressBar($productsToUpdate->count());
        $progressBar->start();

        foreach ($productsToUpdate as $product) {
            if ($product->save()) {
                $progressBar->advance();
            } else {
                $this->error("Failed to save product ID: {$product->id} - {$product->name}");
            }
        }

        $progressBar->finish();
        $this->info(PHP_EOL . 'Slug generation process completed.');

        return Command::SUCCESS;
    }
}
