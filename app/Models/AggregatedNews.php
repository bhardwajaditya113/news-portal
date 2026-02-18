<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AggregatedNews extends Model
{
    use HasFactory;

    protected $table = 'aggregated_news';

    protected $fillable = [
        'news_source_id',
        'external_id',
        'title',
        'slug',
        'content',
        'description',
        'summary',
        'image',
        'image_url',
        'original_url',
        'author',
        'category_id',
        'tags',
        'published_at',
        'fetched_at',
        'sentiment_score',
        'engagement_score',
        'views_count',
        'shares_count',
        'is_featured',
        'is_trending',
        'is_breaking',
        'language',
        'country',
        'location_data',
        'entities',
        'keywords'
    ];

    protected $casts = [
        'tags' => 'array',
        'location_data' => 'array',
        'entities' => 'array',
        'keywords' => 'array',
        'published_at' => 'datetime',
        'fetched_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_trending' => 'boolean',
        'is_breaking' => 'boolean',
    ];

    public function source()
    {
        return $this->belongsTo(NewsSource::class, 'news_source_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get image URL (with fallback to image field)
     */
    public function getImageUrlAttribute($value)
    {
        return $value ?: $this->image;
    }

    public function scopeTrending($query)
    {
        return $query->where('is_trending', true)->orderBy('engagement_score', 'desc');
    }

    public function scopeBreaking($query)
    {
        return $query->where('is_breaking', true)->orderBy('published_at', 'desc');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('published_at', '>=', now()->subHours($hours));
    }
}
