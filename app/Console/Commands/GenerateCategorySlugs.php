<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class GenerateCategorySlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categories:generate-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate slugs for existing categories where slug is null';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting slug generation for categories with null slugs...');

        $categoriesToUpdate = Category::whereNull('slug')->get();

        if ($categoriesToUpdate->isEmpty()) {
            $this->info('No categories found requiring slug generation.');
            return Command::SUCCESS;
        }

        $this->info('Found ' . $categoriesToUpdate->count() . ' categories to update.');
        $progressBar = $this->output->createProgressBar($categoriesToUpdate->count());
        $progressBar->start();

        foreach ($categoriesToUpdate as $category) {
            // Логика генерации слага находится в методе saving() модели Category.
            // Простое сохранение модели вызовет этот обработчик, если slug пуст.
            if ($category->save()) {
                $progressBar->advance();
            } else {
                $this->error("Failed to save category ID: {$category->id} - {$category->name}");
            }
        }

        $progressBar->finish();
        $this->info(PHP_EOL . 'Category slug generation process completed.');

        return Command::SUCCESS;
    }
}
