<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preferred_categories',
        'preferred_sources',
        'preferred_languages',
        'preferred_regions',
        'notification_settings',
        'display_settings',
        'reading_history',
        'saved_articles',
        'interests_keywords'
    ];

    protected $casts = [
        'preferred_categories' => 'array',
        'preferred_sources' => 'array',
        'preferred_languages' => 'array',
        'preferred_regions' => 'array',
        'notification_settings' => 'array',
        'display_settings' => 'array',
        'reading_history' => 'array',
        'saved_articles' => 'array',
        'interests_keywords' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
