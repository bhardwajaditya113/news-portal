<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrendingTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic',
        'slug',
        'category_id',
        'news_count',
        'views_count',
        'engagement_score',
        'sentiment_score',
        'trend_velocity',
        'peak_time',
        'related_keywords',
        'related_news_ids',
        'is_active',
        'language',
        'country'
    ];

    protected $casts = [
        'related_keywords' => 'array',
        'related_news_ids' => 'array',
        'peak_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTop($query, $limit = 10)
    {
        return $query->orderBy('engagement_score', 'desc')->limit($limit);
    }
}
