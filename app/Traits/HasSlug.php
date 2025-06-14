<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Boot trait для автоматической генерации slug
     */
    protected static function bootHasSlug()
    {
        static::saving(function ($model) {
            if ($model->isDirty('name') || empty($model->slug)) {
                $slug = Str::slug($model->name, '-');
                $originalSlug = $slug;
                $count = 1;

                while (static::where('slug', $slug)
                    ->when($model->exists, function ($query) use ($model) {
                        return $query->where('id', '!=', $model->id);
                    })
                    ->when(method_exists(static::class, 'withTrashed'), function ($query) {
                        return $query->withTrashed(); // Include soft deleted records for slug checking
                    })
                    ->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $model->slug = $slug;
            }
        });
    }
}