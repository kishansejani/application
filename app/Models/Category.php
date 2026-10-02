<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_gu',
        'slug',
        'image',
        'icon',
        'description_en',
        'description_gu',
        'sort_order',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name_en) . '-' . rand(100, 999);
            }
        });
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function allSubCategories()
    {
        return $this->hasMany(SubCategory::class)->orderBy('sort_order');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        if ($locale === 'gu' && !empty($this->name_gu)) {
            return $this->name_gu;
        }
        return $this->name_en;
    }

    public function getLocalizedDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'gu' && !empty($this->description_gu)) {
            return $this->description_gu;
        }
        return $this->description_en;
    }

    public function getImageUrlAttribute(): string
    {
        if (empty($this->image)) {
            return asset('images/default-category.png');
        }
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }
        return asset('storage/' . $this->image);
    }
}
