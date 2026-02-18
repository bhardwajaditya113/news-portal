<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Analytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'trackable_type',
        'trackable_id',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'region',
        'device_type',
        'browser',
        'os',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'time_spent',
        'scroll_depth',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function trackable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes for analytics filtering
    public function scopePageViews($query)
    {
        return $query->where('type', 'page_view');
    }

    public function scopeNewsViews($query)
    {
        return $query->where('type', 'news_view');
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month);
    }
}
