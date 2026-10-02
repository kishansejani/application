<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_en',
        'title_gu',
        'subtitle_en',
        'subtitle_gu',
        'image',
        'link_type',
        'link_url',
        'target_id',
        'badge_en',
        'badge_gu',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getLocalizedTitleAttribute(): string
    {
        $locale = app()->getLocale();
        if ($locale === 'gu' && !empty($this->title_gu)) {
            return $this->title_gu;
        }
        return $this->title_en ?? '';
    }

    public function getLocalizedSubtitleAttribute(): string
    {
        $locale = app()->getLocale();
        if ($locale === 'gu' && !empty($this->subtitle_gu)) {
            return $this->subtitle_gu;
        }
        return $this->subtitle_en ?? '';
    }

    public function getLocalizedBadgeAttribute(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'gu' && !empty($this->badge_gu)) {
            return $this->badge_gu;
        }
        return $this->badge_en;
    }

    public function getImageUrlAttribute(): string
    {
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }
        return asset('storage/' . $this->image);
    }
}
