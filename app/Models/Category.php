<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
    ];
    
    /**
     * Добавляем атрибуты, которые должны быть доступны для сериализации JSON
     *
     * @var array
     */
    protected $appends = ['image_url'];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function ($category) {
            if ($category->isDirty('name') || empty($category->slug)) {
                $slug = Str::slug($category->name, '-');
                $originalSlug = $slug;
                $count = 1;

                while (static::where('slug', $slug)->when($category->exists, function ($query) use ($category) {
                    return $query->where('id', '!=', $category->id);
                })->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $category->slug = $slug;
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    /**
     * Получить URL изображения
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }
        
        return asset('images/placeholders/category-placeholder.jpg');
    }
}
