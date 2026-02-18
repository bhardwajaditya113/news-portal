<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // News Sources Table - For RSS/API Feed Management
        Schema::create('news_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('website_url');
            $table->string('rss_feed_url')->nullable();
            $table->enum('api_type', ['rss', 'rest', 'graphql', 'scrape'])->default('rss');
            $table->string('api_key')->nullable();
            $table->string('api_endpoint')->nullable();
            $table->json('category_mapping')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('fetch_interval')->default(30); // minutes
            $table->timestamp('last_fetched_at')->nullable();
            $table->decimal('credibility_score', 3, 2)->default(0.80);
            $table->string('country', 2)->default('US');
            $table->string('language', 5)->default('en');
            $table->integer('priority')->default(50);
            $table->timestamps();
        });

        // Aggregated News Table - For External News Storage
        Schema::create('aggregated_news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_source_id')->constrained('news_sources')->onDelete('cascade');
            $table->string('external_id')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->longText('content')->nullable();
            $table->text('summary')->nullable();
            $table->string('image')->nullable();
            $table->string('original_url');
            $table->string('author')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->json('tags')->nullable();
            $table->timestamp('published_at');
            $table->decimal('sentiment_score', 3, 2)->default(0)->comment('-1 to 1');
            $table->integer('engagement_score')->default(0);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('shares_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_breaking')->default(false);
            $table->string('language', 5)->default('en');
            $table->string('country', 2)->default('US');
            $table->json('location_data')->nullable();
            $table->json('entities')->nullable(); // People, organizations, locations mentioned
            $table->json('keywords')->nullable();
            $table->timestamps();
            
            $table->unique(['news_source_id', 'external_id']);
            $table->index(['is_trending', 'published_at']);
            $table->index(['is_breaking', 'published_at']);
            $table->index(['category_id', 'published_at']);
        });

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            Schema::table('aggregated_news', function (Blueprint $table) {
                $table->fullText(['title', 'summary']);
            });
        }

        // Analytics Table - For Comprehensive Tracking
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // page_view, news_view, click, share, etc.
            $table->string('trackable_type')->nullable();
            $table->unsignedBigInteger('trackable_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('session_id')->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('device_type', 20)->nullable(); // mobile, tablet, desktop
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('referrer')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->integer('time_spent')->nullable(); // seconds
            $table->tinyInteger('scroll_depth')->nullable(); // percentage
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['trackable_type', 'trackable_id']);
            $table->index(['country', 'created_at']);
            $table->index(['type', 'created_at']);
        });

        // User Preferences Table - For Personalization
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('preferred_categories')->nullable();
            $table->json('preferred_sources')->nullable();
            $table->json('preferred_languages')->nullable();
            $table->json('preferred_regions')->nullable();
            $table->json('notification_settings')->nullable();
            $table->json('display_settings')->nullable();
            $table->json('reading_history')->nullable();
            $table->json('saved_articles')->nullable();
            $table->json('interests_keywords')->nullable();
            $table->timestamps();
        });

        // Trending Topics Table
        Schema::create('trending_topics', function (Blueprint $table) {
            $table->id();
            $table->string('topic');
            $table->string('slug');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->integer('news_count')->default(0);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->integer('engagement_score')->default(0);
            $table->decimal('sentiment_score', 3, 2)->default(0);
            $table->decimal('trend_velocity', 8, 2)->default(0); // Rate of growth
            $table->timestamp('peak_time')->nullable();
            $table->json('related_keywords')->nullable();
            $table->json('related_news_ids')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('language', 5)->default('en');
            $table->string('country', 2)->nullable();
            $table->timestamps();

            $table->index(['engagement_score', 'is_active']);
            $table->index(['language', 'country']);
        });

        // Demographics Table - For Reader Demographics
        Schema::create('demographics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('country', 2);
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->unsignedBigInteger('unique_visitors')->default(0);
            $table->unsignedBigInteger('page_views')->default(0);
            $table->decimal('avg_session_duration', 8, 2)->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            $table->json('device_breakdown')->nullable();
            $table->json('browser_breakdown')->nullable();
            $table->json('category_interests')->nullable();
            $table->json('peak_hours')->nullable();
            $table->timestamps();

            $table->unique(['date', 'country', 'region', 'city']);
            $table->index(['date', 'country']);
        });

        // Engagement Metrics Table
        Schema::create('engagement_metrics', function (Blueprint $table) {
            $table->id();
            $table->morphs('engageable');
            $table->date('date');
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('unique_views')->default(0);
            $table->unsignedBigInteger('shares_facebook')->default(0);
            $table->unsignedBigInteger('shares_twitter')->default(0);
            $table->unsignedBigInteger('shares_linkedin')->default(0);
            $table->unsignedBigInteger('shares_whatsapp')->default(0);
            $table->unsignedBigInteger('shares_email')->default(0);
            $table->unsignedBigInteger('comments')->default(0);
            $table->unsignedBigInteger('likes')->default(0);
            $table->decimal('avg_read_time', 6, 2)->default(0);
            $table->decimal('avg_scroll_depth', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['engageable_type', 'engageable_id', 'date']);
        });

        // Real-time Stats Table (for live dashboard)
        Schema::create('realtime_stats', function (Blueprint $table) {
            $table->id();
            $table->string('metric_key');
            $table->string('metric_value');
            $table->json('breakdown')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['metric_key', 'recorded_at']);
        });

        // Breaking News Alerts Table
        Schema::create('breaking_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->nullable()->constrained('news')->onDelete('cascade');
            $table->foreignId('aggregated_news_id')->nullable()->constrained('aggregated_news')->onDelete('cascade');
            $table->string('title');
            $table->text('summary');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->boolean('is_push_sent')->default(false);
            $table->boolean('is_email_sent')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Live Tickers Table
        Schema::create('live_tickers', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // stock, crypto, sports, weather
            $table->string('symbol');
            $table->string('name');
            $table->decimal('value', 20, 8)->nullable();
            $table->decimal('change', 20, 8)->nullable();
            $table->decimal('change_percent', 8, 4)->nullable();
            $table->json('extra_data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_tickers');
        Schema::dropIfExists('breaking_alerts');
        Schema::dropIfExists('realtime_stats');
        Schema::dropIfExists('engagement_metrics');
        Schema::dropIfExists('demographics');
        Schema::dropIfExists('trending_topics');
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('analytics');
        Schema::dropIfExists('aggregated_news');
        Schema::dropIfExists('news_sources');
    }
};
