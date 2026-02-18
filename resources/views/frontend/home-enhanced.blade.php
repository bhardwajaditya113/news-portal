@extends('frontend.layouts.master')

@section('content')
    <!-- Live Breaking News Ticker & Market Data -->
    @include('frontend.home-components.live-ticker')

    <!-- Trending Topics Section -->
    @include('frontend.home-components.trending-section')

    <!-- Hero Featured Stories (Original slider can be kept) -->
    @include('frontend.home-components.hero-slider')

    <!-- Personalized "For You" Section -->
    @include('frontend.home-components.for-you-section')

    <!-- Ad Banner -->
    @if(isset($ad) && $ad->home_top_bar_ad_status == 1)
    <a href="{{ $ad->home_top_bar_ad_url }}">
        <div class="container my-4">
            <div class="ad-banner">
                <img src="{{ $ad->home_top_bar_ad }}" alt="Advertisement" class="img-fluid rounded">
            </div>
        </div>
    </a>
    @endif

    <!-- Category News Sections (BBC/Reuters Style) -->
    @include('frontend.home-components.category-sections')

    <!-- World News Aggregated Section -->
    @include('frontend.home-components.world-news-section')

    <!-- Recent & Most Viewed Sidebar Layout -->
    <section class="recent-sidebar-section py-5">
        <div class="container">
            <div class="row">
                <!-- Recent News Column -->
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <div class="section-header d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">
                            <i class="far fa-clock text-primary mr-2"></i>
                            {{ __('frontend.Recent News') }}
                        </h4>
                        <a href="{{ route('news.index') }}" class="see-all-link">
                            {{ __('frontend.View All') }} <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                    <div class="recent-news-list">
                        @foreach($recentNews ?? [] as $news)
                        <article class="recent-news-item">
                            <a href="{{ route('news.show', $news->slug) }}">
                                <div class="row no-gutters">
                                    <div class="col-md-4">
                                        <div class="news-image">
                                            <img src="{{ asset($news->image) }}" alt="{{ $news->title }}">
                                            @if($news->category)
                                            <span class="category-label">{{ $news->category->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="news-content">
                                            <h5 class="news-title">{{ Str::limit($news->title, 80) }}</h5>
                                            <p class="news-excerpt d-none d-md-block">{{ Str::limit(strip_tags($news->content), 120) }}</p>
                                            <div class="news-meta">
                                                <span class="meta-author">
                                                    <img src="{{ $news->auther->image ?? asset('frontend/assets/images/avatar.png') }}" alt="" class="author-avatar">
                                                    {{ $news->auther->name ?? 'Admin' }}
                                                </span>
                                                <span class="meta-date">
                                                    <i class="far fa-calendar-alt mr-1"></i>
                                                    {{ $news->created_at->format('M d, Y') }}
                                                </span>
                                                <span class="meta-views">
                                                    <i class="far fa-eye mr-1"></i>
                                                    {{ number_format($news->views ?? 0) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </article>
                        @endforeach
                    </div>
                </div>

                <!-- Sidebar Column -->
                <div class="col-lg-4">
                    <!-- Most Viewed -->
                    <div class="sidebar-widget mb-4">
                        <div class="widget-header">
                            <h5 class="mb-0">
                                <i class="fas fa-fire-alt text-danger mr-2"></i>
                                {{ __('frontend.Most Viewed') }}
                            </h5>
                        </div>
                        <div class="widget-content">
                            @foreach($mostViewed ?? [] as $index => $news)
                            <a href="{{ route('news.show', $news->slug) }}" class="most-viewed-item">
                                <span class="rank {{ $index < 3 ? 'top' : '' }}">{{ $index + 1 }}</span>
                                <div class="item-content">
                                    <h6 class="item-title">{{ Str::limit($news->title, 60) }}</h6>
                                    <span class="item-views">{{ number_format($news->views ?? 0) }} views</span>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Newsletter Subscription -->
                    <div class="sidebar-widget newsletter-widget">
                        <div class="widget-header">
                            <h5 class="mb-0">
                                <i class="far fa-envelope text-primary mr-2"></i>
                                {{ __('frontend.Newsletter') }}
                            </h5>
                        </div>
                        <div class="widget-content">
                            <p class="text-muted small">{{ __('frontend.Subscribe to get the latest news delivered to your inbox') }}</p>
                            <form action="{{ route('subscribe-newsletter') }}" method="POST" class="newsletter-form">
                                @csrf
                                <div class="form-group mb-2">
                                    <input type="email" name="email" class="form-control" placeholder="{{ __('frontend.Your email address') }}" required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-paper-plane mr-1"></i> {{ __('frontend.Subscribe') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Social Links -->
                    <div class="sidebar-widget social-widget">
                        <div class="widget-header">
                            <h5 class="mb-0">
                                <i class="fas fa-share-alt text-success mr-2"></i>
                                {{ __('frontend.Follow Us') }}
                            </h5>
                        </div>
                        <div class="widget-content">
                            <div class="social-buttons">
                                @foreach($socialLinks ?? [] as $link)
                                <a href="{{ $link->url }}" target="_blank" class="social-btn" style="background: {{ $link->color ?? '#6366f1' }}">
                                    <i class="{{ $link->icon }}"></i>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
/* Recent News Section */
.recent-sidebar-section {
    background: #f8fafc;
}

.recent-news-item {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    transition: all 0.3s ease;
}

.recent-news-item:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}

.recent-news-item a {
    text-decoration: none;
    color: inherit;
}

.recent-news-item .news-image {
    position: relative;
    height: 180px;
    overflow: hidden;
}

.recent-news-item .news-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.recent-news-item .category-label {
    position: absolute;
    top: 10px;
    left: 10px;
    background: var(--colorPrimary);
    color: #fff;
    padding: 3px 10px;
    font-size: 10px;
    font-weight: 600;
    border-radius: 3px;
    text-transform: uppercase;
}

.recent-news-item .news-content {
    padding: 20px;
}

.recent-news-item .news-title {
    font-size: 16px;
    font-weight: 600;
    line-height: 1.4;
    margin-bottom: 8px;
    color: #1e293b;
}

.recent-news-item .news-excerpt {
    font-size: 13px;
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 12px;
}

.recent-news-item .news-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 12px;
    color: #94a3b8;
}

.author-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 4px;
}

/* Sidebar Widgets */
.sidebar-widget {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}

.widget-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
}

.widget-content {
    padding: 16px 20px;
}

/* Most Viewed */
.most-viewed-item {
    display: flex;
    align-items: flex-start;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
}

.most-viewed-item:last-child {
    border-bottom: none;
}

.most-viewed-item:hover {
    background: #f8fafc;
    margin: 0 -20px;
    padding-left: 20px;
    padding-right: 20px;
}

.most-viewed-item .rank {
    width: 28px;
    height: 28px;
    background: #f1f5f9;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
    color: #64748b;
    margin-right: 12px;
    flex-shrink: 0;
}

.most-viewed-item .rank.top {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff;
}

.most-viewed-item .item-title {
    font-size: 13px;
    font-weight: 600;
    line-height: 1.4;
    margin-bottom: 4px;
    color: #1e293b;
}

.most-viewed-item .item-views {
    font-size: 11px;
    color: #94a3b8;
}

/* Newsletter Widget */
.newsletter-widget {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #fff;
}

.newsletter-widget .widget-header {
    border-bottom-color: rgba(255,255,255,0.2);
}

.newsletter-widget .widget-header h5 {
    color: #fff;
}

.newsletter-widget .widget-header i {
    color: #fff !important;
}

.newsletter-widget .text-muted {
    color: rgba(255,255,255,0.8) !important;
}

.newsletter-widget .form-control {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
}

.newsletter-widget .form-control::placeholder {
    color: rgba(255,255,255,0.6);
}

.newsletter-widget .btn-primary {
    background: #fff;
    color: #6366f1;
    border: none;
}

/* Social Widget */
.social-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.social-btn {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
    transition: all 0.2s;
}

.social-btn:hover {
    opacity: 0.9;
    transform: translateY(-2px);
    color: #fff;
}

/* Responsive */
@media (max-width: 768px) {
    .recent-news-item .news-image {
        height: 150px;
    }
    
    .recent-news-item .news-content {
        padding: 16px;
    }
}
</style>
@endpush
