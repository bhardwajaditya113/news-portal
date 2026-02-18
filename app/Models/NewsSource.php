<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'website_url',
        'rss_feed_url',
        'api_type',
        'api_key',
        'api_endpoint',
        'category_mapping',
        'is_active',
        'fetch_interval',
        'last_fetched_at',
        'credibility_score',
        'country',
        'language',
        'priority'
    ];

    protected $casts = [
        'category_mapping' => 'array',
        'is_active' => 'boolean',
        'last_fetched_at' => 'datetime',
    ];

    public function aggregatedNews()
    {
        return $this->hasMany(AggregatedNews::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }
}
