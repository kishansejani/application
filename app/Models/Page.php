<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title_en',
        'title_gu',
        'content_en',
        'content_gu',
        'meta_title_en',
        'meta_title_gu',
        'meta_description_en',
        'meta_description_gu',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getLocalizedTitleAttribute(): string
    {
        $locale = app()->getLocale();
        if ($locale === 'gu' && !empty($this->title_gu)) {
            return $this->title_gu;
        }
        return $this->title_en;
    }

    public function getLocalizedContentAttribute(): string
    {
        $locale = app()->getLocale();
        if ($locale === 'gu' && !empty($this->content_gu)) {
            return $this->content_gu;
        }
        return $this->content_en;
    }
}
