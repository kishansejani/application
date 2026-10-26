<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ProductImage extends Model
{
    use HasFactory;

    public const TYPE_GALLERY = 'gallery';
    public const TYPE_SPIN = 'spin';
    public const MIN_SPIN_FRAMES = 6;
    public const MAX_SPIN_FRAMES = 72;

    protected $fillable = [
        'product_id',
        'image_path',
        'sort_order',
        'type',
    ];

    private static ?bool $typeColumn = null;

    /** True once the 360° migration has been run (keeps the site working before `php artisan migrate`). */
    public static function supportsTypes(): bool
    {
        if (self::$typeColumn === null) {
            try {
                self::$typeColumn = Schema::hasColumn('product_images', 'type');
            } catch (\Throwable $e) {
                self::$typeColumn = false;
            }
        }
        return self::$typeColumn;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): string
    {
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }
        return asset('storage/' . $this->image_path);
    }
}
