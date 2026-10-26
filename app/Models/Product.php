<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'name_en',
        'name_gu',
        'slug',
        'unit',
        'price',
        'discount_price',
        'stock_quantity',
        'low_stock_threshold',
        'thumbnail',
        'short_description_en',
        'short_description_gu',
        'description_en',
        'description_gu',
        'is_featured',
        'is_active',
        'sku',
    ];

    /**
     * Always load the number of 360° frames so cards / lists can show the
     * "360°" badge without an extra query per product.
     */
    protected $withCount = ['spinFrames'];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name_en) . '-' . rand(1000, 9999);
            }
            if (empty($product->sku)) {
                $product->sku = 'SKU-' . strtoupper(Str::random(6));
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    /** Normal gallery photos (everything that is not a 360° frame). */
    public function images()
    {
        $rel = $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
        if (ProductImage::supportsTypes()) {
            $rel->where('product_images.type', ProductImage::TYPE_GALLERY);
        }
        return $rel;
    }

    /** Ordered frames of the 360° turntable view. */
    public function spinFrames()
    {
        $rel = $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
        if (ProductImage::supportsTypes()) {
            $rel->where('product_images.type', ProductImage::TYPE_SPIN);
        } else {
            $rel->whereRaw('1 = 0'); // migration not run yet -> no 360° frames
        }
        return $rel;
    }

    /** Gallery photos + 360° frames (used when deleting a product). */
    public function allImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    /** A 360° view needs at least 6 frames to feel smooth. */
    public function getHas360Attribute(): bool
    {
        $count = $this->spin_frames_count ?? $this->spinFrames()->reorder()->count();
        return $count >= ProductImage::MIN_SPIN_FRAMES;
    }

    /** @return array<int,string> */
    public function getSpinFrameUrlsAttribute(): array
    {
        return $this->spinFrames->map(fn ($f) => $f->image_url)->values()->all();
    }

    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        if ($locale === 'gu' && !empty($this->name_gu)) {
            return $this->name_gu;
        }
        return $this->name_en;
    }

    public function getLocalizedShortDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'gu' && !empty($this->short_description_gu)) {
            return $this->short_description_gu;
        }
        return $this->short_description_en;
    }

    public function getLocalizedDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'gu' && !empty($this->description_gu)) {
            return $this->description_gu;
        }
        return $this->description_en;
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->discount_price && $this->discount_price > 0 && $this->discount_price < $this->price) {
            return (float) $this->discount_price;
        }
        return (float) $this->price;
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->discount_price && $this->discount_price > 0 && $this->discount_price < $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if ($this->has_discount && $this->price > 0) {
            return (int) round((($this->price - $this->discount_price) / $this->price) * 100);
        }
        return 0;
    }

    public function getIsInStockAttribute(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function getThumbnailUrlAttribute(): string
    {
        if (empty($this->thumbnail)) {
            return asset('images/default-product.png');
        }
        if (filter_var($this->thumbnail, FILTER_VALIDATE_URL)) {
            return $this->thumbnail;
        }
        return asset('storage/' . $this->thumbnail);
    }
}
